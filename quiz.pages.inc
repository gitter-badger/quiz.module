<?php

use Drupal\quiz\Controller\Admin\QuizAdminSettingsController;
use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Form\QuizAdminEntityForm;
use Drupal\quiz\Form\QuizCategorizedForm;
use Drupal\quiz\Form\QuizEntityForm;
use Drupal\quiz\Form\QuizQuestionsForm;
use Drupal\quiz\Form\QuizReportForm;
use Drupal\quiz\Form\QuizTypeForm;

/**
 * Form for /admin/quiz/settings/config.
 */
function quiz_admin_settings_form($form, $form_state) {
  $obj = new QuizAdminSettingsController();
  return $obj->getForm($form, $form_state);
}

function quiz_admin_settings_form_validate($form, $form_state) {
  $obj = new QuizAdminSettingsController();
  return $obj->validate($form, $form_state);
}

function quiz_admin_settings_form_submit($form, $form_state) {
  $obj = new QuizAdminSettingsController();
  return $obj->submit($form, $form_state);
}

function quiz_admin_entity_form($form, $form_state) {
  $obj = new QuizAdminEntityForm();
  return $obj->getForm($form, $form_state);
}

function quiz_admin_entity_form_validate($form, $form_state) {
  $obj = new QuizAdminEntityForm();
  return $obj->validateForm($form, $form_state);
}

function quiz_admin_entity_form_submit($form, $form_state) {
  $obj = new QuizAdminEntityForm();
  return $obj->submitForm($form, $form_state);
}

/**
 * Define quiz-type editing form.
 */
function quiz_type_form($form, &$form_state, $quiz_type, $op = 'edit') {
  $obj = new QuizTypeForm();
  return $obj->get($form, $form_state, $quiz_type, $op);
}

/**
 * Define quiz-entity editing form.
 */
function quiz_entity_form($form, &$form_state, $quiz, $op = 'edit') {
  $obj = new QuizEntityForm($quiz);
  return $obj->get($form, $form_state, $op);
}

function quiz_entity_form_validate($form, &$form_state) {
  $quiz = $form_state['quiz_entity'];
  $obj = new QuizEntityForm($quiz);
  return $obj->validate($form, $form_state);
}

function quiz_entity_form_submit($form, &$form_state) {
  $quiz = $form_state['quiz_entity'];
  $obj = new QuizEntityForm($quiz);
  return $obj->submit($form, $form_state);
}

function quiz_questions_form($form, $form_state, $quiz) {
  $obj = new QuizQuestionsForm();
  $array = $obj->formGet($form, $form_state, $quiz);
  $array['#validate'][] = 'quiz_questions_form_validate';
  return $array;
}

function quiz_questions_form_validate($form, $form_state) {
  $obj = new QuizQuestionsForm();
  return $obj->formValidate($form, $form_state);
}

function quiz_questions_form_submit($form, $form_state) {
  $obj = new QuizQuestionsForm();
  return $obj->formSubmit($form, $form_state);
}

function quiz_categorized_form($form, $form_state, QuizEntity $quiz) {
  $obj = new QuizCategorizedForm();
  return $obj->getForm($form, $form_state, $quiz);
}

function quiz_categorized_form_validate($form, &$form_state) {
  $obj = new QuizCategorizedForm();
  return $obj->formValidate($form, $form_state);
}

function quiz_categorized_form_submit($form, &$form_state) {
  $obj = new QuizCategorizedForm();
  return $obj->formSubmit($form, $form_state);
}

/**
 * Jumper form.
 */
function quiz_jumper_form($form, $form_state, $questions, $current) {
  $form['#attached']['js'][] = drupal_get_path('module', 'quiz') . '/misc/js/quiz.jumper.js';
  $form['question_number'] = array(
      '#type'          => 'select',
      '#options'       => $questions,
      '#default_value' => $current,
  );
  $form['submit'] = array(
      '#type'       => 'submit',
      '#value'      => t('Jump'),
      '#attributes' => array('class' => array('js-hide')),
  );
  return $form;
}

function quiz_jumper_form_submit($form, &$form_state) {
            $quiz = quiz_entity_single_load(__quiz_get_context_id());
            quiz()
              ->getQuizHelper()
              ->getQuestionHelper()
              ->redirect($quiz, $form_state['values']['question_number'] - 1);
            $form_state['redirect'] = "quiz/" . $quiz->qid . "/take/" . $form_state['values']['question_number'];
}
}

/**
 * Pass the correct mark to the theme so that theme authors can use an image.
 *
 * @ingroup themeable
 */
function theme_quiz_answer_result($variables) {
  $type = $variables['type'];
  return '<div class="quiz-score-icon ' . $type . '"></div>';
}

/**
 * Theme a progress indicator for use during a quiz.
 *
 * @param $question_number
 *  The position of the current question in the sessions' array.
 * @param $num_of_question
 *  The number of questions for the quiz.
 * @return
 *  Themed html.
 *
 * @ingroup themeable
 */
function theme_quiz_progress($variables) {
  $output = '';
  $output .= '<div id="quiz-progress">';
  $text = 'Page <span id="quiz-question-number">!x</span> of <span id="quiz-num-questions">@y</span>';
  if ($variables['allow_jumping']) {
    // Show jump form.
    if ($variables['pager']) {
      $output .= theme('quiz_pager', array(
          'quiz'     => $variables['quiz'],
          'siblings' => variable_get('quiz_pager_siblings', 5),
          'current'  => $variables['current'],
          'total'    => count($variables['questions']),
      ));
    }
    else {
      $selectbox = drupal_get_form('quiz_jumper_form', $variables['questions'], $variables['current']);
      $output = t($text, array('!x' => drupal_render($selectbox), '@y' => count($variables['questions'])));
    }
  }
  else {
    // Just text.
    $output .= t($text, array('!x' => $variables['current'], '@y' => count($variables['questions'])));
  }

  $output .= '</div>' . "\n";
  // Add div to be used by jQuery countdown
  if ($variables['time_limit']) {
    $output .= '<div class="countdown"></div>';
  }
  return $output;
}

/**
 * Theme the "no feedback" option.
 *
 * @return
 *  Themed html feedback.
 *
 * @ingroup themeable
 */
function theme_quiz_no_feedback() {
  return t('Thanks for taking the quiz!');
}

/**
 * Theme the stats on the views page
 */
function theme_quiz_view_stats($variables) {
  $quiz = $variables['quiz'];

  // Fetch data
  $stats[] = array('title' => t('Questions'), 'data' => isset($quiz->number_of_questions) ? $quiz->number_of_questions : 0);

  if ($quiz->show_attempt_stats) {
    $takes = $quiz->takes == 0 ? t('Unlimited') : $quiz->takes;
    $stats[] = array('title' => t('Attempts allowed'), 'data' => $takes);
  }

  if ($quiz->quiz_always) {
    $stats[] = array('title' => t('Available'), 'data' => t('Always'));
  }
  else {
    $stats[] = array('title' => t('Opens'), 'data' => format_date($quiz->quiz_open, 'short'));
    $stats[] = array('title' => t('Closes'), 'data' => format_date($quiz->quiz_close, 'short'));
  }

  if (!empty($quiz->pass_rate)) {
    $stats[] = array('title' => t('Pass rate'), 'data' => $quiz->pass_rate . ' %');
  }

  if (!empty($quiz->time_limit)) {
    $stats[] = array('title' => t('Time limit'), 'data' => quiz()->formatDuration($quiz->time_limit));
  }

  $stats[] = array('title' => t('Backwards navigation'), 'data' => $quiz->backwards_navigation ? t('Allowed') : t('Forbidden'));

  // Format and output the data
  $table = '';
  foreach ($stats as $stat) {
    $table .= '<tr>'
      . '<td class="quiz-view-table-title"><strong>' . $stat['title'] . ':</strong></td>'
      . '<td class="quiz-view-table-data"><em>' . $stat['data'] . '</em></td>'
      . '</tr>';
  }
  return '<table id="quiz-view-table">' . $table . '</table>';
}

/**
 * Theme the result page.
 *
 * @param $quiz
 *  The quiz entity object.
 * @param $questions
 *  The questions array as defined by _quiz_get_answers.
 * @param $score
 *  Array of score information as returned by quiz_calculate_score().
 * @param $summary
 *  Filtered text of the summary.
 * @return
 *  Themed html.
 *
 * @ingroup themeable
 */
function theme_quiz_result($variables) {
  global $user;

  $questions = $variables['questions'];
  $score = $variables['score'];
  $summary = $variables['summary'];
  $result = quiz_result_load($variables['result_id']);
  $account = $variables['account'];

  if (!$score['is_evaluated']) {
    $msg = t('Parts of this @quiz have not been evaluated yet. The score below is not final.', array('@quiz' => QUIZ_NAME));
    drupal_set_message($msg, 'warning');
  }

  // Display overall result.
  $params = array(
      '%num_correct'    => $score['numeric_score'],
      '%question_count' => $score['possible_score'],
      '!username'       => ($user->uid == $account->uid) ? t('You') : theme('username', array('account' => $account)),
      '@score'          => $score['percentage_score'],
      '!yourtotal'      => ($user->uid == $account->uid) ? t('Your') : t('Total'),
  );

  $output = '';
  if (quiz()->getQuizHelper()->getFeedbackHelper()->canReview('score', $result)) {
    // Show score.
    $output .= '<div id="quiz_score_possible">' . t('!username got %num_correct of %question_count possible points.', $params) . '</div>' . "\n";
    $output .= '<div id="quiz_score_percent">' . t('!yourtotal score: @score%', $params) . '</div>' . "\n";
  }

  if (quiz()->getQuizHelper()->getFeedbackHelper()->canReview('quiz_feedback', $result)) {
    // Show quiz feedback
    if (isset($summary['passfail'])) {
      $output .= '<div id="quiz_summary">' . $summary['passfail'] . '</div>' . "\n";
    }

    if (isset($summary['result'])) {
      $output .= '<div id="quiz_summary">' . $summary['result'] . '</div>' . "\n";
    }
  }

  $report_form = drupal_get_form('quiz_report_form', $questions);
  return $output . drupal_render($report_form);
}

function quiz_report_form($form, $form_state, $questions) {
  $obj = new QuizReportForm();
  return $obj->getForm($form, $form_state, $questions);
}

function quiz_report_form_submit($form, $form_state) {
  $obj = new QuizReportForm();
  return $obj->formSubmit($form, $form_state);
}

/**
 * Theme the quiz pager.
 */
function theme_quiz_pager($variables) {
  return quiz()
      ->getQuizHelper()
      ->getTakeJumperHelper($variables['quiz'], $variables['total'], $variables['siblings'], $variables['current'])
      ->render();
}

/**
 * Theme a question selection table, adding drag and drop support.
 */
function theme_question_selection_table($variables) {
  $form = $variables['form'];

  // Building headers
  $headers = array(t('Question'), t('Type'), t('Actions'), t('Update'), t('Max score'), t('Auto update max score'), t('Delete'));
  if (isset($form['compulsories'])) {
    $headers[] = t('Compulsory');
  }
  $headers[] = t('Weight');
  $headers[] = t('Parent ID');
  $headers[] = array('data' => t('ID'), 'class' => array('tabledrag-hide'));

  // Building table body
  $rows = array();
  if (!empty($form['titles'])) {
    foreach (element_children($form['titles']) as $id) {
      $form['weights'][$id]['#attributes']['class'] = array('question-list-weight');
      $form['qr_ids'][$id]['#attributes']['class'] = array('qnr-id');
      $form['qr_pids'][$id]['#attributes']['class'] = array('qnr-pid');
      $rows[] = _quiz_get_question_row($form, $id);
    }
    // Make sure the same fields aren't rendered twice
    unset($form['types'], $form['view_links'], $form['remove_links'], $form['stayers']);
    unset($form['max_scores'], $form['auto_update_max_scores'], $form['revision'], $form['weights'], $form['titles'], $form['compulsories'], $form['qr_ids'], $form['qr_pids']);
  }

  // We hide the table if no questions have been added so that jQuery can show
  // it the moment the first question is beeing added.
  $html_attr = array();
  if (isset($form['no_questions'])) {
    $html_attr['style'] = "display:none;";
  }

  drupal_add_tabledrag('question-list', 'match', 'parent', 'qnr-pid', 'qnr-pid', 'qnr-id', TRUE, 1);
  drupal_add_tabledrag('question-list', 'order', 'sibling', 'question-list-weight');

  return drupal_render($form['random_settings'])
    . theme('table', array(
        'header'     => $headers,
        'rows'       => $rows,
        'attributes' => array('id' => 'question-list') + $html_attr))
    . drupal_render_children($form);
}

/**
 * Helper function for theme_question_selection_table
 *
 * TODO: DELETE
 *
 * @see quiz_questions_form()
 * @see theme_question_selection_table()
 *
 * @param $sub_form
 *   Form definition array for a filtered questions list
 * @param $id
 *   Identifier used in $sub_form
 * @return table row
 *   Array defining a table row
 */
function _quiz_get_question_row($sub_form, $id) {
  $type = $sub_form['types'][$id]['#markup'];

  $action = theme('item_list', array(
      'items'      => array(
          drupal_render($sub_form['view_links'][$id]),
          '<SPAN CLASS="q-remove" STYLE="display:none">' . drupal_render($sub_form['remove_links'][$id]) . '</SPAN>',
      ),
      'attributes' => array(
          'class' => array('links', 'inline'),
      ),
  ));
  $qr_pid = $sub_form['qr_pids'][$id]['#default_value'];
  $data_array = array(
      // The checkbox and the title
      ($qr_pid ? theme('indentation', array('size' => 1)) : NULL) . drupal_render($sub_form['titles'][$id]),
      $type,
      $action,
      isset($sub_form['revision'][$id]) ? drupal_render($sub_form['revision'][$id]) : t("Up to date"),
      drupal_render($sub_form['max_scores'][$id]),
      drupal_render($sub_form['auto_update_max_scores'][$id]),
      drupal_render($sub_form['stayers'][$id]),
  );
  if (isset($sub_form['compulsories'])) {
    $data_array[] = drupal_render($sub_form['compulsories'][$id]);
  }
  $data_array[] = drupal_render($sub_form['weights'][$id]);
  $data_array[] = drupal_render($sub_form['qr_pids'][$id]);
  $data_array[] = array(
      'class' => array('tabledrag-hide'),
      'data'  => drupal_render($sub_form['qr_ids'][$id]),
  );

  $leaf_class = $sub_form['types'][$id]['#question_type'] != 'quiz_page' ? 'tabledrag-leaf' : '';

  return array(
      'class' => array('q-row', 'draggable', $leaf_class),
      'id'    => 'q-' . $id,
      'data'  => $data_array
  );
}

/**
 * Themes a categorized quiz form
 */
function theme_quiz_categorized_form($variables) {
  $form = $variables['form'];

  drupal_add_js(drupal_get_path('module', 'quiz') . '/misc/js/quiz.admin.categorized-form.js');

  $rows = array();
  foreach ($form as $key => &$existing) {
    if (!is_numeric($key)) {
      continue;
    }
    $cols = array();
    $cols[] = drupal_render($existing['name']);
    $cols[] = drupal_render($existing['number']);
    $cols[] = drupal_render($existing['max_score']);
    $cols[] = drupal_render($existing['remove']);
    $cols[] = drupal_render($existing['weight']);
    $rows[] = array('data' => $cols, 'class' => array('draggable'));
  }

  $output = '';
  if (!empty($rows)) {
    $header = array(t('Category'), t('Number of questions'), t('Max score per question'), t('Remove'), t('Weight'));
    drupal_add_tabledrag('existing-terms', 'order', 'sibling', 'term-weight', NULL, NULL, TRUE);
    $output .= theme('table', array('header' => $header, 'rows' => $rows, 'attributes' => array('id' => 'existing-terms')));
  }
  return $output . drupal_render_children($form);
}

/**
 * Limit the year options to the years 1970 - 2030 for form items of type date.
 * Some systems don't support all the dates the forms api lets you choose from.
 * This function limits the options to dates most systems support.
 */
function _quiz_after_build_fix_year_options($element) {
  $element['year']['#options'] = drupal_map_assoc(range(1970, 2030));
  return $element;
}
