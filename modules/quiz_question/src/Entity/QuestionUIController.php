<?php

namespace Drupal\quiz_question\Entity;

class QuestionUIController extends \EntityDefaultUIController {

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

    return $items;
  }

}
