<?php

namespace Drupal\quiz_question\Entity;

use DatabaseTransaction;
use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Entity\Relationship;
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

    $question->max_score = $question->getPlugin()->getMaximumScore();
    $question->feedback = !empty($question->feedback) ? $question->feedback : '';
    $question->feedback_format = !empty($question->feedback_format) ? $question->feedback_format : filter_default_format();

    return parent::save($question, $transaction);
  }

  public function load($ids = array(), $conditions = array()) {
    $questions = parent::load($ids, $conditions);

    /* @var $question Question */
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
    $question->qid = $question->qid;
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

  /**
   * Find relationship object between a quiz and a question.
   * @param QuizEntity $quiz
   * @param Question $question
   * @return Relationship
   */
  public function findRelationship(QuizEntity $quiz, Question $question) {
    $conds = array('quiz_vid' => $quiz->vid, 'question_vid' => $question->vid);
    if ($relationships = entity_load('quiz_question_relationship', FALSE, $conds)) {
      return reset($relationships);
    }
  }

}
