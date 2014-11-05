<?php

namespace Drupal\quiz\Entity\QuizEntity;

use Drupal\quiz\Helper\FormHelper;

/**
 * Read and write default properties for quiz entity.
 */
class DefaultPropertiesIO extends FormHelper {

  /**
   * Returns the users default settings.
   *
   * @return
   *   An array of settings. The array is empty in case no settings are available.
   */
  public function getUserDefaultSettings($remove_ids = TRUE) {
    global $user;

    // We found user defaults.
    $conditions = array('status' => -1, 'uid' => $user->uid, 'qid' => 0, 'vid' => 0);
    if ($quizzes = entity_load('quiz_entity', FALSE, $conditions)) {
      $quiz = reset($quizzes);

      if ($remove_ids) {
        $quiz->qid = $quiz->uid = $quiz->vid = $quiz->quiz_open = $quiz->quiz_close = NULL;
      }

      return $quiz;
    }

    return $this->getSystemDefaultSettings($remove_ids);
  }

  public function getSystemDefaultSettings($remove_ids = TRUE) {
    // Found global defaults.
    $conditions = array('status' => -1, 'uid' => 0);
    if ($quizzes = entity_load('quiz_entity', FALSE, $conditions)) {
      $quiz = reset($quizzes);

      if ($remove_ids) {
        $quiz->qid = $quiz->uid = $quiz->vid = $quiz->quiz_open = $quiz->quiz_close = NULL;
      }

      return $quiz;
    }

    return entity_create('quiz_entity', $this->getQuizDefaultSettings());
  }

  /**
   * Returns default values for all quiz settings.
   *
   * @todo also store this in the quiz_node_properties table
   *
   * @return
   *   Array of default values.
   */
  public function getQuizDefaultSettings() {
    return array(
        'status'                     => -1,
        'aid'                        => NULL,
        'allow_jumping'              => 0,
        'allow_resume'               => 1,
        'allow_skipping'             => 1,
        'always_available'           => TRUE,
        'backwards_navigation'       => 1,
        'has_userpoints'             => 0,
        'keep_results'               => 2,
        'mark_doubtful'              => 0,
        'max_score'                  => 0,
        'max_score_for_random'       => 1,
        'number_of_random_questions' => 0,
        'pass_rate'                  => 75,
        'quiz_always'                => 1,
        'quiz_close'                 => 0,
        'quiz_close'                 => $this->prepareDate(NULL, variable_get('quiz_default_close', 30)),
        'quiz_open'                  => 0,
        'quiz_open'                  => $this->prepareDate(),
        'randomization'              => 0,
        'repeat_until_correct'       => 0,
        'review_options'             => array('question' => array(), 'end' => array()),
        'show_attempt_stats'         => 1,
        'show_passed'                => 1,
        'summary_default'            => '',
        'summary_default_format'     => filter_fallback_format(),
        'summary_pass'               => '',
        'summary_pass_format'        => filter_fallback_format(),
        'takes'                      => 0,
        'tid'                        => 0,
        'time_limit'                 => 0,
        'userpoints_tid'             => 0,
    );
  }

}
