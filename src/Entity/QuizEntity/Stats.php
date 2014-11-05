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

}
