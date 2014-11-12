<?php

namespace Drupal\quiz\Entity;

use DatabaseTransaction;
use Drupal\quiz\Entity\Result\ScoreIO;
use Drupal\quiz\Entity\Result\Writer;
use EntityAPIController;

class ResultController extends EntityAPIController {

  private $score_io;

  /** @var Writer */
  private $writer;

  public function getWriter() {
    if (NULL === $this->writer) {
      $this->writer = new Writer();
    }
    return $this->writer;
  }

  /**
   * @return ScoreIO
   */
  public function getScoreIO() {
    if (NULL === $this->score_io) {
      $this->score_io = new ScoreIO();
    }
    return $this->score_io;
  }

  public function setScoreCalculator($score_calculator) {
    $this->score_io = $score_calculator;
    return $this;
  }

  public function load($ids = array(), $conditions = array()) {
    $entities = parent::load($ids, $conditions);

    foreach ($entities as $result) {
      $this->loadLayout($result);
    }

    return $entities;
  }

  /**
   * Attach the layout which previously used to be stored on the result.
   *
   * @param Result $result
   */
  private function loadLayout(Result $result) {
    $layout = entity_load('quiz_result_answer', FALSE, array('result_id' => $result->result_id));

    foreach ($layout as $question) {
      // @kludge
      // This is bulky but now we have to manually find the type and parents of
      // the question. This is the only information that is not stored in the
      // quiz attempt. We reference back to the node relationships for this
      // current version to get the hieararchy.
      $sql = "SELECT n.type, qnr.qr_id, qnr.qr_pid
        FROM {quiz_results} result
          INNER JOIN {quiz_relationship} qnr ON result.quiz_vid = qnr.quiz_vid
          INNER JOIN {node} n ON (qnr.question_nid = n.nid)
        WHERE result.result_id = :result_id AND n.nid = :nid";
      $extra = db_query($sql, array(
          ':result_id' => $result->result_id,
          ':nid'       => $question->question_nid))->fetch();

      $result->layout[$question->number] = array(
          'display_number' => $question->number,
          'nid'            => $question->question_nid,
          'vid'            => $question->question_vid,
          'number'         => $question->number,
          'type'           => $extra->type,
          'qr_id'          => $extra->qr_id,
          'qr_pid'         => $extra->qr_pid,
      );
    }
    ksort($result->layout, SORT_NUMERIC);
  }

  /**
   * @global \stdlClass $user
   * @param Result $result
   * @param DatabaseTransaction $transaction
   */
  public function save($result, DatabaseTransaction $transaction = NULL) {
    global $user;

    $return = parent::save($result, $transaction);

    if (isset($result->original) && !$result->original->is_evaluated && $result->is_evaluated) {
      // Quiz is finished! Delete old results if necessary.
      if ($quiz = quiz_load($result->quiz_qid)) {
        quiz()->getQuizHelper()->getResultHelper()->maintainResult($user, $quiz, $result->result_id);
      }
    }

    return $return;
  }

  public function delete($ids, DatabaseTransaction $transaction = NULL) {
    $return = parent::delete($ids, $transaction);

    $select = db_select('quiz_results_answers', 'answer');
    $select->fields('answer', array('result_id', 'question_nid', 'question_vid'));
    $select->condition('answer.result_id', $ids);
    $result = $select->execute();
    while ($record = $result->fetchAll()) {
      quiz_question_delete_result($record->result_id, $record->question_nid, $record->question_vid);
    }

    db_delete('quiz_results_answers')->condition('result_id', $ids)->execute();
    db_delete('quiz_results')->condition('result_id', $ids)->execute();

    return $return;
  }

}
