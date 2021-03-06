<?php

# ---------------------------------------------------------------
# To be removed
# ---------------------------------------------------------------

/**
 * Implements hook_node_info().
 */
function quiz_question_node_info() {
  $node_info = array();

  foreach (quiz_question_get_plugin_info() as $type => $definition) {
    $node_info[$type] = array(
        'name'        => $definition['name'],
        'base'        => 'quiz_question',
        'description' => $definition['description']
    );
  }

  return $node_info;
}

/**
 * Implements hook_node_presave().
 */
function quiz_question_node_presave($node) {
  foreach (array_keys(quiz_question_get_plugin_info()) as $question_type) {
    if (($node->type === $question_type) && (!drupal_strlen($node->title) || !user_access('edit question titles'))) {
      $body = field_view_field('node', $node, 'body');
      $max_length = variable_get('quiz_autotitle_length', 50);
      $node->title = truncate_utf8(strip_tags(drupal_render($body)), $max_length, TRUE, TRUE);
    }
  }

  if (isset($node->is_quiz_question) && variable_get('quiz_auto_revisioning', 1)) {
    $node->revision = 0;
    if (($plugin = quiz_question_get_provider($node, TRUE)) && $plugin->hasBeenAnswered()) {
      $node->revision = 1;
    }
  }
}

/**
 * Implements hook_node_prepare().
 */
function quiz_question_node_prepare($node) {
  if (isset($node->is_quiz_question) && variable_get('quiz_auto_revisioning', 1)) {
    $node->revision = 0;
    if (($plugin = quiz_question_get_provider($node, TRUE)) && $plugin->hasBeenAnswered()) {
      $node->revision = 1;
    }
  }
}

/**
 * Implements hook_node_revision_delete().
 */
function quiz_question_node_revision_delete($node) {
  foreach (array_keys(quiz_question_get_plugin_info()) as $question_type) {
    if ($node->type === $question_type) {
      _quiz_delete_question($node, TRUE); // true for only this version
    }
  }
}

/**
 * Implements hook_node_access_records().
 */
function quiz_question_node_access_records($node) {
  $grants = array();

  // Restricting view access to question nodes outside quizzes.
  $question_types = array_keys(quiz_question_get_plugin_info());
  if (in_array($node->type, $question_types)) {
    // This grant is for users having 'view quiz question outside of a quiz'
    // permission. We set a priority of 2 because OG has a 1 priority and we
    // want to get around it.
    $grants[] = array(
        'realm'        => 'quiz_question',
        'gid'          => 1,
        'grant_view'   => 1,
        'grant_update' => 0,
        'grant_delete' => 0,
        'priority'     => 2,
    );
  }

  return $grants;
}

/**
 * Implements hook_node_grants().
 */
function quiz_question_node_grants($account, $op) {
  $grants = array();
  if (($op === 'view') && user_access('view quiz question outside of a quiz', $account)) {
    $grants['quiz_question'][] = 1; # Granting view access
  }
  return $grants;
}

/**
 * Implements hook_view().
 */
function quiz_question_view($node, $view_mode) {
  if ($view_mode === 'search_index' && !variable_get('quiz_index_questions', 1)) {
    $node->body = '';
    $node->content = array();
    $node->title = '';
    $node->taxonomy = array();
    return $node;
  }

  if ($view_mode === 'teaser') {
    $node->content['question_teaser'] = array(
        '#prefix' => '<div class="question_type_name">',
        '#markup' => node_type_get_type($node)->name,
        '#suffix' => '</div>',
        '#weight' => -100,
    );
  }
  else {
    // normal node view
    $node->content += quiz_question_get_provider($node, TRUE)->getEntityView();
  }

  return $node;
}

/**
 * Implements hook_validate().
 */
function quiz_question_validate($question, &$form) {
  // Check to make sure that there is a question.
  if (empty($question->body)) {
    form_set_error('body', t('Question text is empty.'));
  }
  quiz_question_get_provider($question)->validateNode($form);
}

/**
 * Implements hook_form_alter().
 */
function quiz_question_form_alter(&$form, $form_state, $form_id) {
  if (!isset($form['#quiz_check_revision_access'])) {
    return;
  }

  // Remove revision fieldset if user don't have access to revise quiz manually.
  if (!user_access('manual quiz revisioning') || variable_get('quiz_auto_revisioning', 1)) {
    $form['revision_information']['revision']['#type'] = 'value';
    $form['revision_information']['revision']['#value'] = $form['revision_information']['revision']['#default_value'];
    $form['revision_information']['log']['#type'] = 'value';
    $form['revision_information']['log']['#value'] = $form['revision_information']['log']['#default_value'];
    $form['revision_information']['#access'] = FALSE;
  }
  unset($form['actions']['preview'], $form['actions']['preview_changes']);
  $form['actions']['submit']['#access'] = TRUE;

  // Quiz questions might want to add a cancel button.
  if (isset($form['#cancel_button'])) {
    $form['actions']['cancel'] = array('#weight' => 6, '#markup' => l(t('Cancel'), $form_state['redirect']));
  }
}
