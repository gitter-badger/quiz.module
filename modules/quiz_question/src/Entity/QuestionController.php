<?php

namespace Drupal\quiz_question\Entity;

use DatabaseTransaction;
use EntityAPIController;

class QuestionController extends EntityAPIController {

  /**
   * @param Question $question
   * @param DatabaseTransaction $transaction
   */
  public function save($question, DatabaseTransaction $transaction = NULL) {
    $is_new = parent::save($question, $transaction);
    $question->getPlugin()->save($is_new);
    return $is_new;
  }

}
