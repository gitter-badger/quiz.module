<?php

namespace Drupal\quiz\Entity;

use Entity;

class Result extends Entity {

  public $result_id;
  public $quiz_qid;
  public $quiz_vid;
  public $uid;
  public $time_start;
  public $time_end;
  public $released;
  public $score;
  public $is_invalid;
  public $is_evaluated;
  public $time_left;
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

    if ($quiz = quiz_entity_single_load(NULL, $this->quiz_vid)) {
      return user_access('score own quiz', $account) && ($quiz->uid == $account->uid);
    }

    return FALSE;
  }

  /**
   * Dtermine if a user has access to view a specific quiz result.
   *
   * @param \Drupal\quiz\Entity\Result|int $result
   * @return boolean
   *  True if access, false otherwise
   */
  public function canAccessOwnResult($account) {
    // Check if the quiz taking has been completed.
    if ($this->time_end > 0 && $this->uid == $account->uid) {
      return TRUE;
    }

    if ($this->canAccessOwnScore($account)) {
      return TRUE;
    }
  }

}
