<?php

namespace Drupal\quiz_question\Entity;

use EntityAPIController;

class QuestionController extends EntityAPIController {

  /**
   * Implements EntityAPIControllerInterface.
   * @param string $hook
   * @param Question $question
   */
  public function invoke($hook, $question) {
    switch ($hook) {
      case 'insert':
        $question->getPlugin()->save($is_new = TRUE);
        break;

      case 'delete':
        $question->getPlugin()->delete($only_this_version = FALSE);
        break;

      case 'revision_delete':
        $question->getPlugin()->delete($only_this_version = TRUE);
        break;
    }

    return parent::invoke($hook, $question);
  }

}
