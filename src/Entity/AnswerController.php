<?php

namespace Drupal\quiz\Entity;

use EntityAPIController;

class AnswerController extends EntityAPIController {

  /**
   * Load answer by Result & questions IDs.
   *
   * @param int $result_id
   * @param int $question_vid
   * @return \Drupal\quiz\Entity\Answer
   */
  public function loadByResultAndQuestion($result_id, $question_vid) {
    $sql = 'SELECT * FROM {quiz_results_answers} WHERE result_id = :result_id AND question_vid = :vid';
    $params = array(':result_id' => $result_id, ':vid' => $question_vid);
    if ($row = db_query($sql, $params)->fetch()) {
      return entity_load_single('quiz_result_answer', $row->result_answer_id);
    }
  }

}
