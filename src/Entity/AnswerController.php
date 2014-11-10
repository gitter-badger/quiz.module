<?php

namespace Drupal\quiz\Entity;

use EntityAPIController;

class AnswerController extends EntityAPIController {

  /**
   * Load answer by Result & questions IDs.
   *
   * @param int $result_id
   * @param int $question_vid
   * @return \Drupal\quiz\Entity\Answer
   */
  public function loadByResultAndQuestion($result_id, $question_vid) {
    $sql = 'SELECT * FROM {quiz_results_answers} WHERE result_id = :result_id AND question_vid = :vid';
    $params = array(':result_id' => $result_id, ':vid' => $question_vid);
    if ($row = db_query($sql, $params)->fetch()) {
      return entity_load_single('quiz_result_answer', $row->result_answer_id);
    }
  }

  /**
   * Get an instance of a quiz question responce.
   *
   * Get information about the class and use it to construct a new
   * object of the appropriate type.
   *
   * @param int $result_id
   *  Result id
   * @param $question
   *  The question node (not a QuizQuestion instance)
   * @param $answer
   *  Resonce to the answering form.
   * @param int $question_nid
   *  Question node id
   * @param int $question_vid
   *  Question node version id
   * @return
   *  The appropriate QuizQuestionResponce extension instance
   */
  public function getInstance($result_id, $question, $answer = NULL, $question_nid = NULL, $question_vid = NULL) {
    // We cache responses to improve performance
    static $responses = array();

    if (is_object($question) && isset($responses[$result_id][$question->vid])) {
      // We refresh the question node in case it has been changed since we cached the response
      $responses[$result_id][$question->vid]->refreshQuestionNode($question);
      if ($responses[$result_id][$question->vid]->is_skipped !== FALSE) {
        return $responses[$result_id][$question->vid];
      }
    }
    elseif (isset($responses[$result_id][$question_vid])) {
      if ($responses[$result_id][$question_vid]->is_skipped !== FALSE) {
        return $responses[$result_id][$question_vid];
      }
    }

    // Prepare to cache responses for this result id
    if (!isset($responses[$result_id])) {
      $responses[$result_id] = array();
    }

    // If the question node isn't set we fetch it from the QuizQuestion instance this responce belongs to
    if (!isset($question)) {
      $question_node = node_load($question_nid, $question_vid);
      $question = _quiz_question_get_instance($question_node, TRUE)->node;
    }

    if (!$question) {
      return FALSE;
    }

    $info = _quiz_question_get_implementations();
    $constructor = $info[$question->type]['response provider'];
    $to_return = new $constructor($result_id, $question, $answer);

    // All responce classes must extend QuizQuestionResponse
    if (!($to_return instanceof QuizQuestionResponse)) {
      $msg = t('The question-response isn\'t a QuizQuestionResponse. It needs to extend the QuizQuestionResponse interface, or extend the abstractQuizQuestionResponse class.');
      drupal_set_message($msg, 'error', FALSE);
    }
    // Cache the responce instance
    $responses[$result_id][$question->vid] = $to_return;

    return $to_return;
  }

}
