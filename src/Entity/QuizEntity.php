<?php

namespace Drupal\quiz\Entity;

use Drupal\quiz\Entity\QuizEntity\QuestionLoader;
use Drupal\quiz_question\Entity\Question;
use Entity;

class QuizEntity extends Entity {

  /** @var int Quiz ID */
  public $qid;

  /** @var int Quiz Revision ID */
  public $vid;

  /** @var int */
  public $status;

  /** @var string The name of the quiz type. */
  public $type = 'quiz';

  /** @var string The quiz label. */
  public $title;

  /** @var integer The user id of the quiz owner. */
  public $uid;

  /** @var integer The Unix timestamp when the quiz was created. */
  public $created;

  /** @var integer The Unix timestamp when the quiz was most recently saved. */
  public $changed;

  /** @var bool */
  public $allow_jumping;

  /** @var array */
  public $resultoptions = array();

  /** @var bool Magic flag to create new revision on save */
  public $is_new_revision;

  /** @var string Revision log */
  public $log;

  /**
   * Enum: QUIZ_KEEP_BEST, QUIZ_KEEP_LATEST, QUIZ_KEEP_ALL.
   *
   * @var int
   */
  public $keep_results = QUIZ_KEEP_ALL;

  /** @var QuestionLoader */
  private $question_loader;

  public function __construct(array $values = array()) {
    parent::__construct($values, 'quiz_entity');
  }

  public function save() {
    global $user;

    // Entity datetime
    $this->changed = time();
    if ($this->is_new = isset($this->is_new) ? $this->is_new : 0) {
      $this->created = time();
      if (NULL === $this->uid) {
        $this->uid = $user->uid;
      }
    }

    // Default properties
    foreach (quiz_controller()->getSettingIO()->getQuizDefaultSettings() as $k => $v) {
      if (!isset($this->{$k})) {
        $this->{$k} = $v;
      }
    }

    return parent::save();
  }

  /**
   * Default quiz entity uri.
   */
  protected function defaultUri() {
    return array('path' => 'quiz/' . $this->identifier());
  }

  /**
   * @return QuestionLoader
   */
  public function getQuestionLoader() {
    if (NULL === $this->question_loader) {
      $this->question_loader = new QuestionLoader($this);
    }
    return $this->question_loader;
  }

  /**
   * Get data for all terms belonging to a Quiz with categorized random questions
   *
   * @return array
   *  Array with all terms that belongs to the quiz as objects
   */
  public function getTermsByVid() {
    return db_query('SELECT td.name, qt.*
        FROM {quiz_terms} qt
        JOIN {taxonomy_term_data} td ON qt.tid = td.tid
        WHERE qt.vid = :vid ORDER BY qt.weight', array(
          ':vid' => $this->vid
      ))->fetchAll();
  }

}
