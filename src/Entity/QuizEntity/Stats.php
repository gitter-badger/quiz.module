<?php

namespace Drupal\quiz\Entity\QuizEntity;

class Stats {

  /**
   * Get a list of all available quizzes.
   *
   * @param int $uid
   *   An optional user ID. If supplied, only quizzes created by that user will be
   *   returned.
   *
   * @return
   *   A list of quizzes.
   */
  public function getQuizzesByUserId($uid = 0) {
    $select = db_select('quiz_entity', 'quiz');
    $select->leftJoin('users', 'u', 'u.uid = quiz.uid');

    if ($uid) {
      $select->condition('quiz.uid', $uid);
    }

    return $select
        ->fields('quiz', array('nid', 'vid', 'title', 'uid', 'created'))
        ->fields('u', array('name'))
        ->orderBy('quiz.qid')
        ->execute()
        ->fetchAllAssoc('qid');
  }

  /**
   * Get the number of compulsory questions for a quiz.
   *
   * @param int $quiz_vid
   * @return int
   *   Number of compulsory questions.
   */
  public function countAlwaysQuestions($quiz_vid) {
    return db_query('SELECT COUNT(*)
      FROM {quiz_relationship} qnr
        JOIN {node} n ON n.nid = qnr.question_nid
      WHERE n.status = 1
        AND qnr.quiz_vid = :quiz_vid
        AND qnr.question_status = :question_status', array(
          ':quiz_vid'        => $quiz_vid,
          ':question_status' => QUESTION_ALWAYS
      ))->fetchField();
  }

  /**
   * Finds out the number of questions for the quiz.
   *
   * Good example of usage could be to calculate the % of score.
   *
   * @param int $quiz_vid
   *   Quiz version ID.
   * @return
   *   Returns the number of quiz questions.
   */
  public function countAllQuestions($quiz_vid) {
    $counter = (int) db_query(# @TODO use countRandomQuestions() method
        'SELECT number_of_random_questions'
        . ' FROM {quiz_entity_revision}'
        . ' WHERE vid = :vid', array(':vid' => $quiz_vid)
      )->fetchField();

    $counter += quiz_controller()->getStats()->countAlwaysQuestions($quiz_vid);

    return $counter;
  }

}
