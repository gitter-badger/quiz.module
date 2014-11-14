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
  public function getPlugin() {
    if (NULL === $this->plugin) {
      $this->plugin = $this->doGetPlugin();
    }
    return $this->plugin;
  }

  /**
   * Get plugin info.
   * @return array
   * @throws \RuntimeException
   */
  public function getPluginInfo() {
    if ($question_type = $this->getQuestionType()) {
      return quiz_question_get_info($question_type->plugin);
    }
    throw new \RuntimeException('Question plugin not found.');
  }

  /**
   * @return \Drupal\quiz_question\QuestionPlugin
   */
  private function doGetPlugin() {
    $plugin_info = $this->getPluginInfo();
    return new $plugin_info['question provider']($this);
  }

  /**
   * Override parent defaultUri method.
   * @return array
   */
  protected function defaultUri() {
    return array('path' => 'quiz-question/' . $this->identifier());
  }

}
