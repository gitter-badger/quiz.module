<?php

namespace Drupal\quiz\Helper;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Helper\Quiz\AccessHelper;
use Drupal\quiz\Helper\Quiz\FeedbackHelper;
use Drupal\quiz\Helper\Quiz\TakeJumperHelper;
use Drupal\quiz\Helper\Quiz\TakingHelper;

class QuizHelper {

  private $accessHelper;
  private $feedbackHelper;
  private $takeJumperHelper;
  private $questionHelper;

  /**
   * @return AccessHelper
   */
  public function getAccessHelper() {
    if (null === $this->accessHelper) {
      $this->accessHelper = new AccessHelper();
    }
    return $this->accessHelper;
  }

  public function setAccessHelper($accessHelper) {
    $this->accessHelper = $accessHelper;
    return $this;
  }

  /**
   * @return FeedbackHelper
   */
  public function getFeedbackHelper() {
    if (null === $this->feedbackHelper) {
      $this->feedbackHelper = new FeedbackHelper();
    }
    return $this->feedbackHelper;
  }

  public function setFeedbackHelper($feedbackHelper) {
    $this->feedbackHelper = $feedbackHelper;
    return $this;
  }

  /**
   * @return TakeJumperHelper
   */
  public function getTakeJumperHelper($quiz, $total, $siblings, $current) {
    if (null == $this->takeJumperHelper) {
      $this->takeJumperHelper = new TakeJumperHelper($quiz, $total, $siblings, $current);
    }
    return $this->takeJumperHelper;
  }

  public function setTakeJumperHelper($takeJumperHelper) {
    $this->takeJumperHelper = $takeJumperHelper;
    return $this;
  }

  /**
   * @return TakingHelper
   */
  public function getQuestionHelper() {
    if (null === $this->questionHelper) {
      $this->questionHelper = new TakingHelper();
    }
    return $this->questionHelper;
  }

  public function setQuestionHelper($questionHelper) {
    $this->questionHelper = $questionHelper;
    return $this;
  }

  /**
   * Find out if a quiz is available for taking or not
   *
   * @param QuizEntity $quiz
   * @return
   *  TRUE if available
   *  Error message(String) if not available
   */
  public function isAvailable(QuizEntity $quiz) {
    global $user;

    if (!$user->uid && $quiz->takes > 0) {
      return t('This @quiz only allows %num_attempts attempts. Anonymous users can only access quizzes that allows an unlimited number of attempts.', array(
          '%num_attempts' => $quiz->takes,
          '@quiz'         => QUIZ_NAME
      ));
    }

    $user_is_admin = entity_access('update', 'quiz_entity', $quiz);
    if ($user_is_admin || $quiz->quiz_always) {
      return TRUE;
    }

    // Compare current GMT time to the open and close dates (which should still be
    // in GMT time).
    if ((REQUEST_TIME >= $quiz->quiz_close) || (REQUEST_TIME < $quiz->quiz_open)) {
      return t('This @quiz is closed.', array('@quiz' => QUIZ_NAME));
    }

    return TRUE;
  }

  /**
   * Check a user/quiz combo to see if the user passed the given quiz.
   *
   * This will return TRUE if the user has passed the quiz at least once, and
   * FALSE otherwise. Note that a FALSE may simply indicate that the user has not
   * taken the quiz.
   *
   * @param int $uid
   * @param int $quiz_qid
   * @param int $quiz_vid
   */
  public function isPassed($uid, $quiz_qid, $quiz_vid) {
    $passed = db_query(
      'SELECT COUNT(result_id) AS passed_count
          FROM {quiz_results} result
            INNER JOIN {quiz_entity_revision} revision
              ON result.quiz_vid = revision.vid AND result.quiz_qid = revision.vid
          WHERE result.quiz_vid = :vid
            AND result.quiz_qid = :qid
            AND result.uid = :uid
            AND score >= pass_rate', array(
        ':vid' => $quiz_vid,
        ':qid' => $quiz_qid,
        ':uid' => $uid
      ))->fetchField();

    // Force into boolean context.
    return ($passed !== FALSE && $passed > 0);
  }

  /**
   * Finds out if a quiz has been answered or not.
   *
   * @return
   *   TRUE if there exists answers to the current question.
   */
  public function isAnswered($node) {
    if (!isset($node->nid)) {
      return FALSE;
    }
    $query = db_select('quiz_results', 'qnr');
    $query->addField('qnr', 'result_id');
    $query->condition('nid', $node->nid);
    $query->condition('vid', $node->vid);
    $query->range(0, 1);
    return $query->execute()->rowCount() > 0;
  }

}
