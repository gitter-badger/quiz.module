<?php

use Drupal\quiz_question\Form\QuestionTypeForm;
use Drupal\quiz_question\Form\RevisionActionsForm;

/**
 * @file
 * User page callbacks for the quiz_question module.
 */
function quiz_question_type_form($form, $form_state, $question_type, $op) {
  $obj = new QuestionTypeForm();
  return $obj->get($form, $form_state, $question_type, $op);
}

function quiz_question_type_form_submit($form, &$form_state) {
  $obj = new QuestionTypeForm();
  return $obj->submit($form, $form_state);
}

/**
 * Submit handler for question type form when user click 'delete' button.
 */
function quiz_question_type_form_submit_delete($form, &$form_state) {
  $form_state['redirect'] = 'admin/structure/quiz-questions/manage/' . $form_state['quiz_question_type']->type . '/delete';
}

/**
 * Callback for /quiz-question/add page.
 */
function quiz_question_adding_landing_page() {
  return theme_render_template(drupal_get_path('module', 'quiz_question') . '/theme/quiz-question-adding-landing.tpl.php', array(
      'question_types' => quiz_question_get_types()
  ));
}

/**
 * Callback for /quiz-question/add/% page.
 */
function quiz_question_adding_page($question_type_name) {
  $question = entity_create('quiz_question', array('type' => $question_type_name));
  return entity_ui_get_form('quiz_question', $question, 'add');
}

/**
 * Get the configuration form for all enabled question types.
 */
function quiz_question_config($form) {
  $form['#validate'] = array();

  // Go through all question types and merge their config forms
  foreach (quiz_question_get_info() as $name => $info) {
    $function = $name . '_quiz_question_config';
    if ($admin_form = $function()) {
      $form[$name] = $admin_form;
      $form[$name]['#type'] = 'fieldset';
      $form[$name]['#title'] = $info['name'];
      $form[$name]['#collapsible'] = TRUE;
      $form[$name]['#collapsed'] = TRUE;
      if (isset($admin_form['#validate']) && is_array($admin_form['#validate'])) {
        $form['#validate'] = array_merge($form['#validate'], $admin_form['#validate']);
        unset($form[$name]['#validate']);
      }
    }
  }

  return system_settings_form($form);
}

/**
 * Callback for /quiz-question/%/%/revision-actions
 */
function quiz_question_revision_actions($form, $form_state, $question_nid, $question_vid) {
  $obj = new RevisionActionsForm();
  return $obj->get($form, $form_state, $question_nid, $question_vid);
}

/**
 * Submit callback for the revision actions page
 */
function quiz_question_revision_actions_submit($form, &$form_state) {
  $obj = new RevisionActionsForm();
  return $obj->submit($form, $form_state);
}
