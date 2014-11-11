<?php

/**
 * Load question type.
 *
 * @param string $name
 * @return \Drupal\quiz_question\Entity\QuestionType
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
 * @param string $type
 * @param stdClass $account
 */
function quiz_question_access_callback() {
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
