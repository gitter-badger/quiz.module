<?php

namespace Drupal\quiz\Helper\Node;

class NodeInsertHelper extends NodeHelper {

  public function execute($quiz) {
    // Need to set max_score if this is a cloned node
    $max_score = 0;

    // Copy all the questions belonging to the quiz if this is a new translation.
    if ($quiz->is_new && isset($quiz->translation_source)) {
      $this->copyQuestions($quiz);
    }

    // Add references to all the questions belonging to the quiz if this is a
    // cloned quiz (node_clone compatibility)
    if ($quiz->is_new && isset($quiz->clone_from_original_qid)) {
      $old_quiz = quiz_load($quiz->clone_from_original_qid, NULL, TRUE);
      $max_score = $old_quiz->max_score;
      $questions = $old_quiz->getQuestionIO()->getQuestionList();

      // Format the current questions for referencing
      foreach ($questions as $question) {
        $nid = $question['nid'];
        $questions[$nid]->state = $question->question_status;
        $questions[$nid]->refresh = 0;
      }

      quiz()->getQuizHelper()->setQuestions($quiz, $questions);
    }

    $this->presaveActions($quiz);

    // If the quiz is saved as not randomized we have to make sure that
    // questions belonging to the quiz are saved as not random
    $this->checkNumRandom($quiz);
    $this->checkNumAlways($quiz);

    quiz_controller()->getSettingIO()->updateUserDefaultSettings($quiz);
  }

  /**
   * Copies questions when a quiz is translated.
   *
   * @param $quiz
   *   The new translated quiz entity.
   */
  private function copyQuestions($quiz) {
    // Find original questions.
    $query = db_query('
        SELECT question_nid, question_vid, question_status, weight, max_score, auto_update_max_score
        FROM {quiz_relationship}
        WHERE quiz_vid = :quiz_vid', array(':quiz_vid' => $quiz->translation_source->vid));
    foreach ($query as $relationship) {
      $this->copyQuestion($quiz, $relationship);
    }
  }

  private function copyQuestion($quiz, $relationship) {
    $original_question = quiz_question_entity_load($relationship->question_nid);

    // Set variables we can't or won't carry with us to the translated node to NULL.
    $original_question->nid = $original_question->vid = $original_question->created = $original_question->changed = NULL;
    $original_question->revision_timestamp = $original_question->menu = $original_question->path = NULL;
    $original_question->files = array();
    if (isset($original_question->book['mlid'])) {
      $original_question->book['mlid'] = NULL;
    }

    // Set the correct language.
    $original_question->language = $quiz->language;

    // Save the node.
    node_save($original_question);

    // Save the relationship between the new question and the quiz.
    db_insert('quiz_relationship')
      ->fields(array(
          'quiz_qid'              => $quiz->qid,
          'quiz_vid'              => $quiz->vid,
          'question_nid'          => $original_question->nid,
          'question_vid'          => $original_question->vid,
          'question_status'       => $relationship->question_status,
          'weight'                => $relationship->weight,
          'max_score'             => $relationship->max_score,
          'auto_update_max_score' => $relationship->auto_update_max_score,
      ))
      ->execute();
  }

}
