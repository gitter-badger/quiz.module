<?php

namespace Drupal\quiz_question\Entity;

use Entity;

class Question extends Entity {

  /** @var int */
  public $qid;

  /** @var int */
  public $vid;

  /** @var string */
  public $type;

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

  public function getPlugin() {
    return quiz_question_get_plugin($this);
  }

}
