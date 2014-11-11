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
