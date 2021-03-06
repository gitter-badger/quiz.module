<?php

namespace Drupal\quiz\Entity\QuizEntity;

use Drupal\quiz\Helper\FormHelper;
use Drupal\quiz\Entity\QuizEntity;

/**
 * Read and write default properties for quiz entity.
 *
 * We have a small trick here: status = -1
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
   * @return mixed[]
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
        'quiz_open'                  => 0,
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

  /**
   * Update quiz's default settings for context user.
   * @global \stdClass $user
   * @param QuizEntity $_quiz
   */
  public function updateUserDefaultSettings(QuizEntity $_quiz) {
    global $user;

    $quiz = clone $_quiz;

    $quiz->aid = !empty($quiz->aid) ? $quiz->aid : 0;
    $quiz->summary_pass = is_array($quiz->summary_pass) ? $quiz->summary_pass['value'] : $quiz->summary_pass;
    $quiz->summary_pass_format = is_array($quiz->summary_pass) ? $quiz->summary_pass['format'] : isset($quiz->summary_pass_format) ? $quiz->summary_pass_format : filter_fallback_format();
    $quiz->summary_default = is_array($quiz->summary_default) ? $quiz->summary_default['value'] : $quiz->summary_default;
    $quiz->summary_default_format = is_array($quiz->summary_default) ? $quiz->summary_default['format'] : isset($quiz->summary_default_format) ? $quiz->summary_default_format : filter_fallback_format();
    $quiz->tid = isset($quiz->tid) ? $quiz->tid : 0;

    if (!empty($quiz->remember_settings)) {
      // Save user defaults.
      $user_quiz = clone $quiz;
      $user_quiz->uid = $user->uid;

      // Find ID of old entry.
      $conditions = array('status' => -1, 'uid' => $user->uid, 'qid' => 0, 'vid' => 0);
      if ($quizzes = entity_load('quiz_entity', FALSE, $conditions)) {
        $_user_quiz = reset($quizzes);
        $user_quiz->qid = $_user_quiz->qid;
        $user_quiz->vid = $_user_quiz->vid;
      }
      else {
        $user_quiz->qid = $user_quiz->vid = NULL;
      }

      $this->saveQuizSettings($user_quiz);
    }

    if (!empty($quiz->remember_global)) {
      $system_quiz = clone $quiz;
      $system_quiz->uid = 0;

      // Find ID of old entry
      $conditions = array('status' => -1, 'uid' => 0);
      if ($quizzes = entity_load('quiz_entity', FALSE, $conditions, TRUE)) {
        $_system_quiz = reset($quizzes);
        $system_quiz->qid = $_system_quiz->qid;
        $system_quiz->vid = $_system_quiz->vid;
      }
      else {
        $system_quiz->qid = $system_quiz->vid = NULL;
      }

      return $this->saveQuizSettings($system_quiz);
    }
  }

  /**
   * Insert or update the quiz entity properties accordingly.
   */
  private function saveQuizSettings(QuizEntity $quiz) {
    $quiz->title = '';
    $quiz->status = -1;
    return $quiz->save();
  }

}
