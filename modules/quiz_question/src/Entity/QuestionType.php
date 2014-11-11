<?php

namespace Drupal\quiz_question\Entity;

use Entity;

class QuestionType extends Entity {

  /** @var string */
  public $type;

  /** @var string */
  public $label;

  /** @var string */
  public $plugin;

  /** @var string */
  public $description;

  /** @var string */
  public $help;

  /** @var int */
  public $weight = 0;

  public function __construct(array $values = array()) {
    parent::__construct($values, 'quiz_question_type');
  }

  /**
   * Returns whether the question type is locked, thus may not be deleted or renamed.
   *
   * Quiz types provided in code are automatically treated as locked, as well
   * as any fixed question type.
   */
  public function isLocked() {
    return isset($this->status) && empty($this->is_new) && (($this->status & ENTITY_IN_CODE) || ($this->status & ENTITY_FIXED));
  }

}
