<?php

namespace Drupal\quiz_question\Entity;

use EntityDefaultUIController;

class QuestionUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    // Custom default structure by entity.module
    $items['admin/content/quiz-questions']['type'] = MENU_LOCAL_TASK;

    // Change path from /admin/content/quiz/add -> /quizz/add
    unset($items['admin/content/quiz-questions/add']);
    $items['quiz-question/add'] = array(
        'title'            => 'Add question',
        'access callback'  => 'entity_access',
        'access arguments' => array('create', 'quiz_question'),
        'file path'        => drupal_get_path('module', 'quiz_question'),
        'file'             => 'quiz_question.pages.inc',
        'page callback'    => 'quiz_question_adding_landing_page',
    );

    foreach (array_keys(quiz_question_get_types()) as $name) {
      $items['quiz-question/add/' . str_replace('_', '-', $name)] = array(
          'title callback'   => 'entity_ui_get_action_title',
          'title arguments'  => array('add', 'quiz_question'),
          'access callback'  => 'entity_access',
          'access arguments' => array('create', 'quiz_question'),
          'page callback'    => 'quiz_question_adding_page',
          'page arguments'   => array($name),
          'file path'        => drupal_get_path('module', 'quiz_question'),
          'file'             => 'quiz_question.pages.inc',
      );
    }

    return $items;
  }

}
