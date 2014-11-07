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
  public function updateTotalScore(QuizEntity $quiz, $result_id) {
    global $user;

    $score = quiz_result_controller()->getScoreCalculator()->calculate($quiz, $result_id);
    db_update('quiz_results')
      ->fields(array('score' => $score['percentage_score']))
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
  public function loadAnswer($result_id, $question_nid, $question_vid) {
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

}
