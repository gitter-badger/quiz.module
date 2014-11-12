<?php

namespace Drupal\quiz_question\Entity;

use EntityAPIController;

class QuestionController extends EntityAPIController {

  public function save($entity, \DatabaseTransaction $transaction = NULL) {
    $return = parent::save($entity, $transaction);
    return $return;
  }

}
