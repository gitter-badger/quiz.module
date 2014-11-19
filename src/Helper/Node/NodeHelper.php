<?php

namespace Drupal\quiz\Helper\Node;

abstract class NodeHelper {

  /**
   * Common actions that need to be done before a quiz is inserted or updated
   *
   * @param $quiz
   *   Quiz entity
   */
  protected function presaveActions(&$quiz) {
    if (empty($quiz->pass_rate)) {
      $quiz->pass_rate = 0;
    }

    if ($quiz->randomization < 2) {
      $quiz->number_of_random_questions = 0;
    }
  }

  /**
   * If a quiz is saved as not randomized we should make sure all random questions
   * are converted to always.
   *
   * @param \Drupal\quiz\Entity\QuizEntity $quiz
   */
  protected function checkNumRandom(&$quiz) {
    if ($quiz->randomization == 2) {
      return;
    }

    db_delete('quiz_relationship')
      ->condition('question_status', QUIZ_QUESTION_RANDOM)
      ->condition('quiz_vid', $quiz->vid)
      ->execute();
  }

  /**
   * If a quiz is saved with random categories we should make sure all questions
   * are removed from the quiz
   *
   * @param \Drupal\quiz\Entity\QuizEntity $quiz
   */
  protected function checkNumAlways($quiz) {
    if ($quiz->randomization != 3) {
      return;
    }
    db_delete('quiz_relationship')
      ->condition('quiz_vid', $quiz->vid)
      ->execute();
  }

}
