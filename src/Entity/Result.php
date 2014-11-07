<?php

namespace Drupal\quiz\Entity;

use Entity;

class Result extends Entity {

  public $result_id;
  public $quiz_qid;
  public $quiz_vid;

  /** @var int Author ID */
  public $uid;

  /** @var int Start timestamp */
  public $time_start;

  /** @var int End timestamp */
  public $time_end;

  /** @var bool */
  public $released;
  public $score;

  /** @var bool */
  public $is_invalid;

  /** @var bool */
  public $is_evaluated;

  /** @var int */
  public $time_left;

  /** @var array */
  public $layout = array();

  public function countPages() {
    $count = 0;
    foreach ($this->layout as $item) {
      if (('quiz_page' === $item['type']) || !$item['qr_pid']) {
        $count++;
      }
    }
    return $count;
  }

  public function isLastPage($page_number) {
    return $page_number == $this->countPages();
  }

  public function getNextPageNumber($page_number) {
    if ($this->isLastPage($page_number)) {
      return $page_number;
    }
    return $page_number + 1;
  }

  public function getPageItem($page_number) {
    $number = 0;
    foreach ($this->layout as $item) {
      if (('quiz_page' === $item['type']) || !$item['qr_pid']) {
        if (++$number == $page_number) {
          return $item;
        }
      }
    }
  }

  /**
   * Checks if the user has access to save score for his quiz.
   */
  public function canAccessOwnScore($account) {
    if (user_access('score any quiz', $account)) {
      return TRUE;
    }

    if ($quiz = quiz_load(NULL, $this->quiz_vid)) {
      return user_access('score own quiz', $account) && ($quiz->uid == $account->uid);
    }

    return FALSE;
  }

  /**
   * Dtermine if a user has access to view a specific quiz result.
   *
   * @return boolean
   *  True if access, false otherwise
   */
  public function canAccessOwnResult($account) {
    // Check if the quiz taking has been completed.
    if ($this->time_end > 0 && $this->uid == $account->uid) {
      return TRUE;
    }

    return $this->canAccessOwnScore($account) ? TRUE : FALSE;
  }

}
