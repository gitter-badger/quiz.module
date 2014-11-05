<?php

namespace Drupal\quiz\Helper\Quiz;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Helper\FormHelper;

class SettingHelper extends FormHelper {

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

      // Find ID of old entry
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
  public function saveQuizSettings(QuizEntity $quiz) {
    $quiz->title = '';
    $quiz->status = -1;
    return entity_save('quiz_entity', $quiz);
  }

}
