<?php

namespace Drupal\quiz\Helper\Quiz;

use Drupal\quiz\Entity\QuizEntity;

class ResultHelper {

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
