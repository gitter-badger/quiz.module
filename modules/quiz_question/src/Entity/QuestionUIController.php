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

    return $items;
  }

}
