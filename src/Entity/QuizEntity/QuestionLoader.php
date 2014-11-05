<?php

namespace Drupal\quiz\Entity\QuizEntity;

use Drupal\quiz\Entity\QuizEntity;

class QuestionLoader {

  private $quiz;

  public function __construct(QuizEntity $quiz) {
    $this->quiz = $quiz;
  }

  /**
   * Builds the questionlist for quizzes with categorized random questions
   */
  public function buildCategoziedQuestionList() {
    if (!$question_types = array_keys(quiz_get_question_types())) {
      return array();
    }

    $questions = array();
    $nids = array();
    $total_count = 0;
    foreach ($this->quiz->getTermsByVid() as $term) {
      $query = db_select('node', 'n');
      $query->join('taxonomy_index', 'tn', 'n.nid = tn.nid');
      $query->fields('n', array('nid', 'vid'));
      $query->fields('tn', array('tid'));
      $query->condition('n.status', 1);
      $query->condition('n.type', $question_types);
      $query->condition('tn.tid', $term->tid);
      if (!empty($nids)) {
        $query->condition('n.nid', $nids, 'NOT IN');
      }
      $query->range(0, $term->number);
      $query->orderBy('RAND()');

      $result = $query->execute();
      $count = 0;
      while ($question = $result->fetchAssoc()) {
        $count++;
        $question['tid'] = $term->tid;
        $question['number'] = $count + $total_count;
        $questions[] = $question;
        $nids[] = $question['nid'];
      }
      $total_count += $count;
      if ($count < $term->number) {
        return array(); // Not enough questions
      }
    }
    return $questions;
  }

  /**
   * Retrieves a list of questions (to be taken) for a given quiz.
   *
   * If the quiz has random questions this function only returns a random
   * selection of those questions. This function should be used to decide
   * what questions a quiz taker should answer.
   *
   * This question list is stored in the user's result, and may be different
   * when called multiple times. It should only be used to generate the layout
   * for a quiz attempt and NOT used to do operations on the questions inside of
   * a quiz.
   *
   * @return array[] Array of question info.
   */
  public function getQuestionList() {
    if ($this->quiz->randomization == 3) {
      $questions = $this->buildCategoziedQuestionList();
    }
    else {
      $questions = $this->getRequiredQuestions();
    }

    $count = 0;
    $display_count = 0;
    $questions_out = array();
    foreach ($questions as &$question) {
      $display_count++;
      $question['number'] = ++$count;
      if ($question['type'] !== 'quiz_page') {
        $question['display_number'] = $display_count;
      }
      $questions_out[$count] = $question;
    }
    return $questions_out;
  }

  private function getRequiredQuestions() {
    $questions = array();

    // Get required questions first.
    $query = db_query('SELECT n.nid, n.vid, n.type, qnr.qr_id, qnr.qr_pid
        FROM {quiz_relationship} qnr
          JOIN {node} n ON qnr.question_nid = n.nid
          LEFT JOIN {quiz_relationship} qnr2 ON (qnr.qr_pid = qnr2.qr_id OR (qnr.qr_pid IS NULL AND qnr.qr_id = qnr2.qr_id))
        WHERE qnr.quiz_vid = :quiz_vid
          AND qnr.question_status = :question_status
          AND n.status = 1
        ORDER BY qnr2.weight, qnr.weight', array(
        ':quiz_vid'        => $this->quiz->vid,
        ':question_status' => QUESTION_ALWAYS
    ));
    $i = 0;
    while ($question_node = $query->fetchAssoc()) {
      // Just to make it easier on us, let's use a 1-based index.
      $i++;
      $questions[$i] = $question_node;
    }

    // Get random questions for the remainder.
    if ($this->quiz->number_of_random_questions > 0) {
      $random_questions = $this->getRandomQuestions();
      $questions = array_merge($questions, $random_questions);
      if ($this->quiz->number_of_random_questions > count($random_questions)) {
        // Unable to find enough requested random questions.
        return FALSE;
      }
    }

    // Shuffle questions if required.
    if ($this->quiz->randomization > 0) {
      shuffle($questions);
    }

    return $questions;
  }

  /**
   * Get an array list of random questions for a quiz.
   *
   * @return array[] Array of nid/vid combos for quiz questions.
   */
  private function getRandomQuestions() {
    $num_random = $this->quiz->number_of_random_questions;
    $tid = $this->quiz->tid;
    $questions = array();
    if ($num_random > 0) {
      if ($tid > 0) {
        $questions = quiz()->getQuizHelper()->getRandomTaxonomyQuestionIds($tid, $num_random);
      }
      else {
        // Select random question from assigned pool.
        $result = db_query_range(
          "SELECT question_nid as nid, question_vid as vid, n.type
          FROM {quiz_relationship} qnr
          JOIN {node} n on qnr.question_nid = n.nid
          WHERE qnr.quiz_vid = :quiz_vid
          AND qnr.quiz_qid = :quiz_qid
          AND qnr.question_status = :question_status
          AND n.status = 1
          ORDER BY RAND()", 0, $this->quiz->number_of_random_questions, array(
            ':quiz_vid'        => $this->quiz->vid,
            ':quiz_qid'        => $this->quiz->qid,
            ':question_status' => QUESTION_RANDOM
          )
        );
        while ($question_node = $result->fetchAssoc()) {
          $question_node['random'] = TRUE;
          $question_node['relative_max_score'] = $this->quiz->max_score_for_random;
          $questions[] = $question_node;
        }
      }
    }
    return $questions;
  }

  /**
   * Retrieve list of published questions assigned to quiz.
   *
   * This function should be used for question browsers and similiar... It should
   * not be used to decide what questions a user should answer when taking a
   * quiz. quiz_build_question_list is written for that purpose.
   *
   * @return
   *   An array of questions.
   */
  public function getQuestions() {
    $questions = array();
    $query = db_select('node', 'n');
    $query->fields('n', array('nid', 'type'));
    $query->fields('nr', array('vid', 'title'));
    $query->fields('qnr', array('question_status', 'weight', 'max_score', 'auto_update_max_score', 'qr_id', 'qr_pid'));
    $query->addField('n', 'vid', 'latest_vid');
    $query->join('node_revision', 'nr', 'n.nid = nr.nid');
    $query->leftJoin('quiz_relationship', 'qnr', 'nr.vid = qnr.question_vid');
    $query->condition('n.status', 1);
    $query->condition('qnr.quiz_qid', $this->quiz->qid);
    if ($this->quiz->vid) {
      $query->condition('qnr.quiz_vid', $this->quiz->vid);
    }
    $query->condition('qr_pid', NULL, 'IS');
    $query->orderBy('qnr.weight');

    $result = $query->execute();
    foreach ($result as $question) {
      $questions[] = $question;
      $this->getSubQuestions($question->qr_id, $questions);
    }

    foreach ($questions as &$node) {
      $node = $this->reloadQuestion($node);
    }

    return $questions;
  }

  private function getSubQuestions($qr_pid, &$questions) {
    $query = db_select('node', 'n');
    $query->fields('n', array('nid', 'type'));
    $query->fields('nr', array('vid', 'title'));
    $query->fields('qnr', array('question_status', 'weight', 'max_score', 'auto_update_max_score', 'qr_id', 'qr_pid'));
    $query->addField('n', 'vid', 'latest_vid');
    $query->innerJoin('node_revision', 'nr', 'n.nid = nr.nid');
    $query->innerJoin('quiz_relationship', 'qnr', 'nr.vid = qnr.question_vid');
    $query->condition('qr_pid', $qr_pid);
    $query->orderBy('weight');
    $result = $query->execute();
    foreach ($result as $question) {
      $questions[] = $question;
    }
  }

  /**
   * Map node properties to a question object.
   *
   *  This was 'quiz_node_map($node)' before.
   *
   * @param $node
   *  The question node.
   *
   * @return
   *  Question object.
   */
  private function reloadQuestion($node) {
    $question = node_load($node->nid, $node->vid);

    // Append extra fields.
    $question->latest_vid = $node->latest_vid;
    $question->question_status = isset($node->question_status) ? $node->question_status : QUESTION_NEVER;
    if (isset($node->max_score)) {
      $question->max_score = $node->max_score;
    }
    if (isset($node->auto_update_max_score)) {
      $question->auto_update_max_score = $node->auto_update_max_score;
    }
    $question->weight = $node->weight;
    $question->qr_id = $node->qr_id;
    $question->qr_pid = $node->qr_pid;

    return $question;
  }

}
