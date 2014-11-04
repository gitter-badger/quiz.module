<?php

namespace Drupal\quiz\Form;

use stdClass;

class QuizAdminEntityForm {

  public function getForm($form, $form_state) {
    // basic form
    $dummy_quiz = entity_create('quiz_entity', array('is_fake' => TRUE) + (array) quiz()->getQuizHelper()->getSettingHelper()->getQuizDefaultSettings());
    $entity_form = new QuizEntityForm($dummy_quiz);
    $form = $entity_form->get($form, $form_state, 'add');

    $form['direction'] = array(
        '#markup' => t('Here you can change the default quiz settings for new users.'),
        '#weight' => -10,
    );

    // unset values we can't or won't let the user edit default values for
    unset($form['#quiz_check_revision_access']);
    unset($form['title']);
    unset($form['body_field']);
    unset($form['taking']['aid']);
    unset($form['taking']['addons']);
    unset($form['quiz_availability']['quiz_open']);
    unset($form['quiz_availability']['quiz_close']);
    unset($form['resultoptions']);
    unset($form['number_of_random_questions']);
    unset($form['remember_global']);

    $form['remember_settings']['#type'] = 'value';
    $form['remember_settings']['#default_value'] = TRUE;
    $form['submit'] = array('#type' => 'submit', '#value' => t('Save'));

    return $form;
  }

  public function validateForm($form, &$form_state) {
    // Create dummy quiz for quiz_validate
    $dummy_quiz = new stdClass();
    foreach ($form_state['values'] as $key => $value) {
      $dummy_quiz->{$key} = $value;
    }
    $dummy_quiz->resultoptions = array();

    // We use quiz_validate to validate the default values
    quiz_validate($dummy_quiz);
  }

  public function submitForm($form, &$form_state) {
    // We add the uid for the "default user"
    // Generate the quiz object:
    $quiz = (object) $form_state['values'];
    $quiz->uid = 0;
    $quiz->qid = 0;
    $quiz->vid = 0;
    quiz()->getQuizHelper()->getSettingHelper()->saveQuizSettings($quiz);
    $form_state['quiz'] = $quiz;
  }

}
