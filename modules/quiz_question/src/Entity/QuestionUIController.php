<?php

namespace Drupal\quiz_question\Entity;

class QuestionUIController extends \EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();

    // Define custom menu items
    // …

    return $items;
  }

}
