<?php

namespace Drupal\quiz_question\Entity;

use Entity;

class Question extends Entity {

  /** @var int */
  public $qid;

  /** @var int */
  public $vid;

  /** @var int Legacy question ID. */
  public $nid;

  /** @var string */
  public $type;

  /** @var \Drupal\quiz_question\QuestionPlugin */
  private $plugin;

  /** @var bool */
  public $status;

  /** @var string */
  public $title;

  /** @var int */
  public $created;

  /** @var int */
  public $changed;

  /** @var int */
  public $uid;

  /** @var int */
  public $revision_uid;

  /** @var int */
  public $log;

  /** @var int */
  public $max_score;

  /** @var string */
  public $feedback;

  /** @var string */
  public $feedback_format;

  /**
   * @return \Drupal\quiz_question\QuestionPlugin
   */
  public function getPlugin() {
    if (NULL === $this->plugin) {
      $this->plugin = $this->doGetPlugin();
    }
    return $this->plugin;
  }

  /**
   * Get question type object.
   *
   * @return \Drupal\quiz_question\Entity\QuestionType
   */
  public function getQuestionType() {
    return quiz_question_type_load($this->type);
  }

  /**
   * @return \Drupal\quiz_question\QuestionPlugin
   */
  private function doGetPlugin() {
    if ($question_type = $this->getQuestionType()) {
      $plugin_info = quiz_question_get_info($question_type->plugin);
      return new $plugin_info['question provider']($this);
    }
    throw new \RuntimeException('Question plugin not found.');
  }

}
