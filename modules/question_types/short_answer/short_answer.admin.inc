<?php

/**
 * short_answer.admin
 *
 * @file
 */

/**
 * Generate a view of all unscored short_answer questions.
 *
 * @see theme_short_answer_view_unscored()
 */
function short_answer_view_unscored() {
  $unscored = ShortAnswerResponse::fetchAllUnscoredAnswers();
  return theme('short_answer_view_unscored', array('unscored' => $unscored));
}

/**
 * Page handler for displaying a scoring form.
 * This function is called directly from the menu router. It generates a form for
 * scoring a quiz.
 *
 * @param int $question_vid
 * @param int $result_id
 * @return string
 */
function short_answer_edit_score($question_vid, $result_id) {
  if (!$question = quiz_question_entity_load(NULL, $question_vid)) {
    return drupal_not_found();
  }

  if ('short_answer' !== $question->getQuestionType()->plugin) {
    throw new \RuntimeException('Unexpected question type: ' . $question->getQuestionType()->plugin);
  }

  if (!$answer = (object) short_answer_get_answer($question->qid, $question->vid, $result_id)) {
    return drupal_not_found();
  }

  $answer->rel_score = 0;
  if ($question->max_score > 0) {
    $answer->rel_score = round($answer->score * $answer->rel_max_score / $question->max_score);
  }

  drupal_set_title(t('Score answer to "@title"', array('@title' => $question->title)), PASS_THROUGH);
  return drupal_get_form('short_answer_score_form', $question, $answer);
}

/**
 * Build a form for scoring long-answer questions.
 *
 * @param \Drupal\quiz_question\Entity\Question $question
 * @param $answer
 *  An object containing an answer to the question. This form is for scoring that answer.
 * @return
 *  The form (as a FAPI array).
 */
function short_answer_score_form($form, $context, $question, $answer) {
  if (!$question || $question->type != 'short_answer' || !$answer) {
    drupal_not_found();
    return;
  }
  // Set up the form
  $form['question'] = array(
      '#type'   => 'item',
      '#title'  => t('Question'),
      '#markup' => check_markup($question->body['und'][0]['value'], $question->body['und'][0]['format']),
  );
  $form['answer'] = array(
      '#type'   => 'item',
      '#title'  => t('Answer'),
      '#markup' => check_markup($answer->answer, variable_get('short_answer_markup_filter')),
  );
  $form['show_max_score'] = array(
      '#type'   => 'item',
      '#title'  => t('Maximum Score'),
      '#markup' => (int) $answer->rel_max_score,
  );
  $form['score'] = array(
      '#type'          => 'textfield',
      '#title'         => t('Score'),
      '#description'   => t('The score for this essay, between 0 and @max', array('@max' => $answer->rel_max_score)),
      '#size'          => 3,
      '#maxlength'     => 3,
      '#default_value' => (int) $answer->rel_score,
      '#required'      => TRUE,
  );
  $form['answer_feedback'] = array(
      '#title'         => t('Feedback'),
      '#type'          => 'text_format',
      '#description'   => t('The text entered here would be shown to attendees'),
      '#default_value' => $answer->answer_feedback,
      '#format'        => isset($answer->answer_feedback_format) ? $answer->answer_feedback_format : NULL
  );
  $form['submit'] = array(
      '#type'  => 'submit',
      '#value' => t('Save this score'),
  );

  // Save some work by keeping these.
  $form['max_score'] = array(
      '#type'  => 'value',
      '#value' => $question->max_score,
  );
  $form['rel_max_score'] = array(
      '#type'  => 'value',
      '#value' => $answer->rel_max_score,
  );
  $form['question_nid'] = array(
      '#type'  => 'value',
      '#value' => $answer->question_nid,
  );
  $form['question_vid'] = array(
      '#type'  => 'value',
      '#value' => $answer->question_vid,
  );
  $form['result_id'] = array(
      '#type'  => 'value',
      '#value' => $answer->result_id,
  );

  return $form;
}

/**
 * Validate the score form
 */
function short_answer_score_form_validate($form, $form_state) {
  // Check to make sure that entered score is not higher than max allowed score.
  $max = (int) $form_state['values']['rel_max_score'];
  $given = (int) $form_state['values']['score'];

  if (!quiz_valid_integer($form_state['values']['score'], 0, $max)) {
    $args = array('@score' => $given, '@max' => $max);
    form_error($form['score'], t('The score needs to be a number between 0 and @max', $args));
  }
}

/**
 * Submit the score form
 */
function short_answer_score_form_submit($form, &$form_state) {
  $result = quiz_result_load($form_state['values']['result_id']);

  $return = short_answer_score_an_answer($result, array(
      'quiz'            => $result->getQuiz(),
      'vid'             => $form_state['values']['question_vid'],
      'score'           => $form_state['values']['score'],
      'max_score'       => $form_state['values']['rel_max_score'],
      'answer_feedback' => $form_state['values']['answer_feedback']
  ));

  if ($return == 1) {
    drupal_set_message("The score has been saved.");
    $form_state['redirect'] = 'admin/quiz/reports/score-short-answer';
  }
  else {
    drupal_set_message(t('Error saving the score. The selected answer was not scored.'), 'error');
  }
}
