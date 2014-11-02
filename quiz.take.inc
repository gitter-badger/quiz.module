<?php

/**
 * @file quiz.take.inc
 *
 * Functions to render and process quiz-taking pages.
 */

/**
 * Jumper form.
 */
function quiz_jumper_form($form, $form_state, $questions, $current) {
  $form['#attached']['js'][] = drupal_get_path('module', 'quiz') . '/js/quiz.jumper.js';

  $form['question_number'] = array(
      '#type'          => 'select',
      '#options'       => $questions,
      '#default_value' => $current,
  );

  $form['submit'] = array(
      '#type'       => 'submit',
      '#value'      => t('Jump'),
      '#attributes' => array('class' => array('js-hide')),
      '#submit'     => array(function($form, &$form_state) {
            $quiz = quiz_entity_single_load(__quiz_get_context_id());
            quiz()
              ->getQuizHelper()
              ->getQuestionHelper()
              ->redirect($quiz, $form_state['values']['question_number'] - 1);
            $form_state['redirect'] = "quiz/" . $quiz->qid . "/take/" . $form_state['values']['question_number'];
      }),
  );
  return $form;
}
