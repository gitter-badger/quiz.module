<?php

namespace Drupal\quiz_question\Entity;

use DatabaseTransaction;
use EntityAPIController;

class QuestionController extends EntityAPIController {

  /**
   * Implements EntityAPIControllerInterface.
   *
   * @param Question $question
   * @param DatabaseTransaction $transaction
   */
  public function save($question, DatabaseTransaction $transaction = NULL) {
    if (isset($question->feedback) && is_array($question->feedback)) {
      $question->feedback_format = $question->feedback['format'];
      $question->feedback = $question->feedback['value'];
    }
    return parent::save($question, $transaction);
  }

  public function load($ids = array(), $conditions = array()) {
    $questions = parent::load($ids, $conditions);

    /* @var $question \Drupal\quiz_question\Entity\Question */
    foreach ($questions as $question) {
      foreach ($question->getPlugin()->load() as $k => $v) {
        $question->$k = $v;
      }
    }

    return $questions;
  }

  /**
   * Implements EntityAPIControllerInterface.
   *
   * @param string $hook
   * @param Question $question
   */
  public function invoke($hook, $question) {
    $this->legacyFixQuestionId($question);

    switch ($hook) {
      case 'insert':
        $question->getPlugin()->save($is_new = TRUE);
        break;

      case 'update':
        $question->getPlugin()->save($is_new = FALSE);
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
   * @TODO Remove legacy code
   * @param Question $question
   */
  private function legacyFixQuestionId(Question $question) {
    $question->nid = $question->qid;
  }

  /**
   * Implements EntityAPIControllerInterface.
   * @param Question $question
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
