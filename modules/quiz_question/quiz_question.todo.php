<?php

use Drupal\quiz_question\Entity\Question;
use Drupal\quiz_question\Entity\QuestionType;

# ---------------------------------------------------------------
# To be completed
# ---------------------------------------------------------------

/**
 * Implements hook_permission()
 */
function quiz_question_permisison() {
  $perms = array();

  $perms['administer quiz questions'] = array(
      'title'           => t('Administer quiz questions'),
      'description'     => t('Have all permissions on all questions.'),
      'restrict access' => TRUE,
  );

  return $perms;
}

/**
 * Get all question types.
 */
function quiz_question_get_types() {
  return entity_load_multiple_by_name('quiz_question_type');
}

function quiz_question_entity_load($id = NULL, $vid = NULL, $reset = FALSE) {
  if (NULL === $vid) {
    return entity_load_single('quiz_question', $id);
  }

  $results = entity_load('quiz_question', array(), array('vid' => $vid), $reset);
  if (!empty($results)) {
    return reset($results);
  }
}

/**
 * Load question type.
 *
 * @param string $name
 * @return QuestionType
 */
function quiz_question_type_load($name) {
  $types = entity_load_multiple_by_name('quiz_question_type', array($name));
  return isset($types[$name]) ? $types[$name] : NULL;
}

function quiz_question_type_access() {
  return TRUE;
}

/**
 * Access callback for question entity.
 *
 * @param string $op
 * @param Question|null $question
 * @param stdClass $account
 */
function quiz_question_access_callback($op, $question = NULL, $account = NULL, $entity_type = '') {
  switch ($op) {
    case 'create':
      return user_access('create question content', $account);
    case 'update':
      return user_access('edit any question content', $account);
    case 'view':
      return user_access('access question', $account);
  }
  return TRUE;
}
