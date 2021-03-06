<?php

namespace Drupal\quiz\Helper\Quiz;

use Drupal\quiz\Entity\QuizEntity;

class TakingHelper {

  /**
   * Update the session for this quiz to the active question.
   *
   * @param QuizEntity $quiz
   * @param int $page_number
   *   Question number starting at 1.
   */
  public function redirect(QuizEntity $quiz, $page_number) {
    $_SESSION['quiz'][$quiz->qid]['current'] = $page_number;
  }

}
