<?php

namespace Drupal\quiz_question\Form;

use Drupal\quiz_question\Entity\Question;

class QuestionForm {

  public function get($form, &$form_state, Question $question, $op) {
    return $form;
  }

  public function validate($form, &$form_state) {

  }

  public function submit($form, &$form_state) {

  }

}
