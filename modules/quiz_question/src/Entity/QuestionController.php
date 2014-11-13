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

  /**
   * @param \Drupal\quiz_question\Entity\Question $question
   * @param string $view_mode
   * @param string $langcode
   * @param string $content
   */
  public function buildContent($question, $view_mode = 'full', $langcode = NULL, $content = array()) {
    if ('teaser' !== $view_mode) {
      $content += $question->getPlugin()->getEntityView();
    }
    return parent::buildContent($question, $view_mode, $langcode, $content);
  }

}
