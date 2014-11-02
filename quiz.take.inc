<?php

/**
 * @file quiz.take.inc
 *
 * Functions to render and process quiz-taking pages.
 */

/**
 * Actions to take at the end of a quiz
 *
 * @param \Drupal\quiz\Entity\QuizEntity $quiz
 *  The quiz node
 * @param int $score
 *  Score as a number
 */
function quiz_end_actions($quiz, $score, $session_data) {
  // Call hook_quiz_finished().
  // @TODO consider hook_entity_update if we make quiz results rules capable
  module_invoke_all('quiz_finished', $quiz, $score, $session_data);

  // Lets piggy back here to perform the quiz defined action since were done
  // with this quiz.
  // We will verify that there is an associated action with this quiz and then
  // perform that action.
  if (!empty($quiz->aid)) {
    // @TODO get rid of this. Replace with rules. Make quiz results entities or
    // something
    // Some actions are reliant on objects and I am unsure which ones, for now I
    // have simply passed the actions_do() function an empty array. By passing
    // this function a single id then it will retrieve the callback, get the
    // parameters and perform that function (action) for you.
    actions_do($quiz->aid, $quiz, $score, $session_data);
  }

  return $score;
}

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
