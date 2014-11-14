<?php

namespace Drupal\quiz\Helper;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Helper\Quiz\AccessHelper;
use Drupal\quiz\Helper\Quiz\FeedbackHelper;
use Drupal\quiz\Helper\Quiz\ResultHelper;
use Drupal\quiz\Helper\Quiz\TakeJumperHelper;
use Drupal\quiz\Helper\Quiz\TakingHelper;
use stdClass;

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
   * @param QuizEntity $quiz
   * @param \Drupal\quiz_question\Entity\Question $question
   */
  public function addQuestion($quiz, $question) {
    $quiz_questions = $quiz->getQuestionLoader()->getQuestions();

    // Do not add a question if it's already been added (selected in an earlier checkbox)
    foreach ($quiz_questions as $q) {
      if ($question->vid == $q->vid) {
        return FALSE;
      }
    }

    // Otherwise let's add a relationship!
    $question->quiz_qid = $quiz->qid;
    $question->quiz_vid = $quiz->vid;
    quiz_question_get_plugin($question)->saveRelationships();
    quiz_controller()->getMaxScoreWriter()->update(array($quiz->vid));
  }

  /**
   * Sets the questions that are assigned to a quiz.
   *
   * @param \Drupal\quiz\Entity\QuizEntity $quiz
   * @param \stdClass[] $questions
   *   An array of questions.
   * @param bool $set_new_revision
   *   If TRUE, a new revision will be generated. Note that saving
   *   quiz questions unmodified will still generate a new revision of the quiz if
   *   this is set to TRUE. Why? For a few reasons:
   *   - All of the questions are updated to their latest VID. That is supposed to
   *     be a feature.
   *   - All weights are updated.
   *   - All status flags are updated.
   *
   * @return
   *   Boolean TRUE if update was successful, FALSE otherwise.
   */
  public function setQuestions(&$quiz, $questions, $set_new_revision = FALSE) {
    if ($set_new_revision) {
      $quiz->is_new_revision = 1; # Create a new Quiz VID, even if nothing changed.
      $quiz->save();
    }

    // When node_save() calls all of the node API hooks, old quiz info is
    // automatically inserted into quiz_relationship. We could get clever and
    // try to do strategic updates/inserts/deletes, but that method has already
    // proven error prone as the module has gained complexity (See 5.x-2.0-RC2).
    // So we go with the brute force method:
    db_delete('quiz_relationship')
      ->condition('quiz_qid', $quiz->qid)
      ->condition('quiz_vid', $quiz->vid)
      ->execute();

    if (empty($questions)) {
      return TRUE; // This is not an error condition.
    }

    foreach ($questions as $question) {
      if ($question->state == QUESTION_NEVER) {
        continue;
      }

      // Update to latest OR use the version given.
      $question_vid = $question->vid;
      if ($question->refresh) {
        $question_vid = db_query('SELECT vid FROM {node} WHERE nid = :nid', array(
            ':nid' => $question->nid))->fetchField();
      }

      $question_inserts[$question->qr_id] = array(
          'quiz_qid'              => $quiz->qid,
          'quiz_vid'              => $quiz->vid,
          'question_nid'          => $question->nid,
          'question_vid'          => $question_vid,
          'question_status'       => $question->state,
          'weight'                => $question->weight,
          'max_score'             => (int) $question->max_score,
          'auto_update_max_score' => (int) $question->auto_update_max_score,
          'qr_pid'                => $question->qr_pid,
          'qr_id'                 => !$set_new_revision ? $question->qr_id : NULL,
          'old_qr_id'             => $question->qr_id,
      );
      drupal_write_record('quiz_relationship', $question_inserts[$question->qr_id]);
    }

    // Update the parentage when a new revision is created.
    // @todo this is copy pasta from quiz_update_quiz_question_relationship
    foreach ($question_inserts as $question_insert) {
      db_update('quiz_relationship')
        ->condition('qr_pid', $question_insert['old_qr_id'])
        ->condition('quiz_vid', $quiz->vid)
        ->condition('quiz_qid', $quiz->qid)
        ->fields(array('qr_pid' => $question_insert['qr_id']))
        ->execute();
    }

    quiz_controller()->getMaxScoreWriter()->update(array($quiz->vid));
    return TRUE;
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

  /**
   * @param string $question_type
   * @return string
   *   Name of module matching the question type, as given by quiz_question_info()
   *   hook.
   */
  public function getQuestionModuleFromType($question_type) {
    $types = quiz_get_question_types();
    if (!isset($types[$question_type])) {
      drupal_set_message(t('The module for the questiontype %type is not enabled', array('%type' => $question_type)), 'warning');
      return FALSE;
    }
    return $types[$question_type]['module'];
  }

}
