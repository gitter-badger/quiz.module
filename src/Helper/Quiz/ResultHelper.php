<?php

namespace Drupal\quiz\Helper\Quiz;

use Drupal\quiz\Entity\QuizEntity;

class ResultHelper {

  /**
   * Update a score for a quiz.
   *
   * This updates the quiz entity results table.
   *
   * It is used in cases where a quiz score is changed after the quiz has been
   * taken. For example, if a long answer question is scored later by a human,
   * then the quiz should be updated when that answer is scored.
   *
   * Important: The value stored in the table is the *percentage* score.
   *
   * @param $quiz
   *   The quiz entity for the quiz that is being scored.
   * @param $result_id
   *   The result ID to update.
   * @return
   *   The score as an integer representing percentage. E.g. 55 is 55%.
   */
  public function updateTotalScore($quiz, $result_id) {
    global $user;

    $score = $this->calculateScore($quiz, $result_id);
    db_update('quiz_results')
      ->fields(array(
          'score' => $score['percentage_score'],
      ))
      ->condition('result_id', $result_id)
      ->execute();

    if ($score['is_evaluated']) {
      // Call hook_quiz_scored().
      module_invoke_all('quiz_scored', $quiz, $score, $result_id);

      $this->maintainResult($user, $quiz, $result_id);
      db_update('quiz_results')
        ->fields(array('is_evaluated' => 1))
        ->condition('result_id', $result_id)
        ->execute();
    }

    return $score['percentage_score'];
  }

  /**
   * Load a specific result answer.
   */
  public function loadAnswerResult($result_id, $question_nid, $question_vid) {
    $sql = 'SELECT * '
      . ' FROM {quiz_results_answers} '
      . ' WHERE result_id = :result_id '
      . '   AND question_nid = :nid '
      . '   AND question_vid = :vid';
    $params = array(':result_id' => $result_id, ':nid' => $question_nid, ':vid' => $question_vid);
    if ($row = db_query($sql, $params)->fetch()) {
      return entity_load_single('quiz_result_answer', $row->result_answer_id);
    }
  }

  /**
   * Calculates the score user received on quiz.
   *
   * @param $quiz
   *   The quiz entity.
   * @param $result_id
   *   Quiz result ID.
   *
   * @return array
   *   Contains three elements: question_count, num_correct and percentage_score.
   */
  public function calculateScore($quiz, $result_id) {
    // 1. Fetch all questions and their max scores
    $questions = db_query('SELECT a.question_nid, a.question_vid, n.type, r.max_score
      FROM {quiz_results_answers} a
      LEFT JOIN {node} n ON (a.question_nid = n.nid)
      LEFT OUTER JOIN {quiz_relationship} r ON (r.question_vid = a.question_vid) AND r.quiz_vid = :vid
      WHERE result_id = :rid', array(':vid' => $quiz->vid, ':rid' => $result_id));

    // 2. Callback into the modules and let them do the scoring. @todo after 4.0: Why isn't the scores already saved? They should be
    // Fetched from the db, not calculated....
    $scores = array();
    $count = 0;
    foreach ($questions as $question) {
      // Questions picked from term id's won't be found in the quiz_relationship table
      if ($question->max_score === NULL && isset($quiz->tid) && $quiz->tid > 0) {
        $question->max_score = $quiz->max_score_for_random;
      }

      // Invoke hook_quiz_question_score().
      // We don't use module_invoke() because (1) we don't necessarily want to wed
      // quiz type to module, and (2) this is more efficient (no NULL checks).
      $mod = quiz_question_module_for_type($question->type);
      if (!$mod) {
        continue;
      }
      $function = $mod . '_quiz_question_score';

      if (function_exists($function)) {
        // Allow for max score to be considered.
        $scores[] = $function($quiz, $question->question_nid, $question->question_vid, $result_id);
      }
      else {
        drupal_set_message(t('A @quiz question could not be scored: No scoring info is available', array('@quiz' => QUIZ_NAME)), 'error');
        $dummy_score = new stdClass();
        $dummy_score->possible = 0;
        $dummy_score->attained = 0;
        $scores[] = $dummy_score;
      }
      ++$count;
    }

    // 3. Sum the results.
    $possible_score = 0;
    $total_score = 0;
    $is_evaluated = TRUE;
    foreach ($scores as $score) {
      $possible_score += $score->possible;
      $total_score += $score->attained;
      if (isset($score->is_evaluated)) {
        // Flag the entire quiz if one question has not been evaluated.
        $is_evaluated &= $score->is_evaluated;
      }
    }

    // 4. Return the score.
    return array(
        'question_count'   => $count,
        'possible_score'   => $possible_score,
        'numeric_score'    => $total_score,
        'percentage_score' => ($possible_score == 0) ? 0 : round(($total_score * 100) / $possible_score),
        'is_evaluated'     => $is_evaluated,
    );
  }

  /**
   * Deletes results for a quiz according to the keep results setting
   *
   * @param QuizEntity $quiz
   *  The quiz entity to be maintained
   * @param int $result_id
   *  The result id of the latest result for the current user
   * @return
   *  TRUE if results where deleted.
   */
  public function maintainResult($account, $quiz, $result_id) {
    // Do not delete results for anonymous users
    if ($account->uid == 0) {
      return;
    }

    switch ($quiz->keep_results) {
      case QUIZ_KEEP_ALL:
        return FALSE;
      case QUIZ_KEEP_BEST:
        $best_result_id = db_query(
          'SELECT result_id FROM {quiz_results}
           WHERE quiz_qid = :qid
             AND uid = :uid
             AND is_evaluated = :is_evaluated
           ORDER BY score DESC', array(
            ':qid'          => $quiz->qid,
            ':uid'          => $account->uid,
            ':is_evaluated' => 1))->fetchField();
        if (!$best_result_id) {
          return;
        }

        $res = db_query('SELECT result_id
          FROM {quiz_results}
          WHERE quiz_qid = :qid
            AND uid = :uid
            AND result_id != :best_rid
            AND is_evaluated = :is_evaluated', array(
            ':qid'          => $quiz->qid,
            ':uid'          => $account->uid,
            ':is_evaluated' => 1,
            ':best_rid'     => $best_result_id
        ));
        $result_ids = array();
        while ($result_id2 = $res->fetchField()) {
          $result_ids[] = $result_id2;
        }
        entity_delete_multiple('quiz_result', $result_ids);
        return !empty($result_ids);
      case QUIZ_KEEP_LATEST:
        $res = db_query('SELECT result_id
            FROM {quiz_results}
            WHERE quiz_qid = :qid
              AND uid = :uid
              AND is_evaluated = :is_evaluated
              AND result_id != :result_id', array(
            ':qid'          => $quiz->qid,
            ':uid'          => $account->uid,
            ':is_evaluated' => 1,
            ':result_id'    => $result_id
        ));
        $result_ids = array();
        while ($result_id2 = $res->fetchField()) {
          $result_ids[] = $result_id2;
        }
        entity_delete_multiple('quiz_result', $result_ids);
        return !empty($result_ids);
    }
  }

  /**
   * Delete quiz responses for quizzes that haven't been finished.
   *
   * This was _quiz_delete_old_in_progress()
   *
   * @param $quiz
   *   A quiz entity where old in progress results shall be deleted.
   * @param $uid
   *   The userid of the user the old in progress results belong to.
   */
  public function deleteIncompletedResultsByUserId($quiz, $uid) {
    $res = db_query('SELECT qnr.result_id
          FROM {quiz_results} qnr
          WHERE qnr.uid = :uid
            AND qnr.quiz_qid = :qid
            AND qnr.time_end = :time_end
            AND qnr.quiz_vid < :vid', array(
        ':uid'      => $uid,
        ':qid'      => $quiz->qid,
        ':time_end' => 1,
        ':vid'      => $quiz->vid));
    $result_ids = array();
    while ($result_id = $res->fetchField()) {
      $result_ids[] = $result_id;
    }
    entity_delete_multiple('quiz_result', $result_ids);
  }

}
