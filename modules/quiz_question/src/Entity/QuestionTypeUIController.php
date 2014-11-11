<?php

namespace Drupal\quiz_question\Entity;

class QuestionTypeUIController extends \EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();
    $items[$this->path]['description'] = strtr('Manage !quiz question types, including fields.', array('!quiz' => QUIZ_NAME));
    return $items;
  }

}
