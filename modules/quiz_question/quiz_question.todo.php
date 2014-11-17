<?php

use Drupal\quiz_question\Entity\Question;
use Drupal\quiz_question\Entity\QuestionType;

# ---------------------------------------------------------------
# To be completed
# ---------------------------------------------------------------

/**
 * Implements hook_permission()
 */
function quiz_question_permission() {
  $perms = array();

  $perms['administer quiz questions'] = array(
      'title'           => t('Administer quiz questions'),
      'description'     => t('Have all permissions on all questions.'),
      'restrict access' => TRUE,
  );

  foreach (quiz_question_get_types() as $name => $info) {
    $perms += array(
        "create $name question"     => array(
            'title' => t('%type_name: Create new question', array('%type_name' => $info->label)),
        ),
        "edit own $name question"   => array(
            'title' => t('%type_name: Edit own question', array('%type_name' => $info->label)),
        ),
        "edit any $name question"   => array(
            'title' => t('%type_name: Edit any question', array('%type_name' => $info->label)),
        ),
        "delete own $name question" => array(
            'title' => t('%type_name: Delete own question', array('%type_name' => $info->label)),
        ),
        "delete any $name question" => array(
            'title' => t('%type_name: Delete any question', array('%type_name' => $info->label)),
        ),
    );
  }

  return $perms;
}

/**
 * Get all question types.
 *
 * @return \Drupal\quiz_question\Entity\QuestionType[]
 */
function quiz_question_get_types() {
  return entity_load_multiple_by_name('quiz_question_type');
}

/**
 * Load question entity.
 *
 * @param int $id
 * @param int $vid
 * @param bool $reset
 * @return \Drupal\quiz_question\Entity\Question
 */
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
