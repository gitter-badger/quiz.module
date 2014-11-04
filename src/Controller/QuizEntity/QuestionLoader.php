<?php

namespace Drupal\quiz\Entity\QuizEntity;

use Drupal\quiz\Entity\QuizEntity;

class QuestionLoader {

  private $quiz;

  public function __construct(QuizEntity $quiz) {
    $this->quiz = $quiz;
  }

  /**
   * Get an array list of random questions for a quiz.
   *
   * @return array[] Array of nid/vid combos for quiz questions.
   */
  public function getRandomQuestions() {
    $num_random = $this->quiz->number_of_random_questions;
    $tid = $this->quiz->tid;
    $questions = array();
    if ($num_random > 0) {
      if ($tid > 0) {
        $questions = $this->getRandomTaxonomyQuestionIds($tid, $num_random);
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
   * Get data for all terms belonging to a Quiz with categorized random questions
   *
   * @return array
   *  Array with all terms that belongs to the quiz as objects
   */
  public function getTermsByVid() {
    return db_query('SELECT td.name, qt.*
        FROM {quiz_terms} qt
        JOIN {taxonomy_term_data} td ON qt.tid = td.tid
        WHERE qt.vid = :vid ORDER BY qt.weight', array(
          ':vid' => $this->quiz->vid
      ))->fetchAll();
  }

}
