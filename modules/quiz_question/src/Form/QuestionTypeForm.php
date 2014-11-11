<?php

namespace Drupal\quiz_question\Form;

class QuestionTypeForm {

  public function get($form, &$form_state, \Drupal\quiz_question\Entity\QuestionType $question_type, $op) {
    if ($op === 'clone') {
      $question_type->label .= ' (cloned)';
      $question_type->type = '';
    }

    $provider_options = array();
    foreach (quiz_question_get_info() as $name => $info) {
      $provider_options[$name] = $info['name'];
    }

    $form['plugin'] = array(
        '#type'        => 'select',
        '#required'    => TRUE,
        '#title'       => t('Question plugin'),
        '#description' => t('Can not be changed after question type created.'),
        '#options'     => $provider_options,
        '#disabled'    => !empty($question_type->provider),
    );

    $form['label'] = array(
        '#type'          => 'textfield',
        '#title'         => t('Label'),
        '#default_value' => $question_type->label,
        '#description'   => t('The human-readable name of this question type.'),
        '#required'      => TRUE,
        '#size'          => 30,
    );

    $form['description'] = array(
        '#type'          => 'textarea',
        '#title'         => t('Description'),
        '#description'   => t('Describe this question type. The text will be displayed on the Add new question page.'),
        '#default_value' => $question_type->description,
    );

    $form['help'] = array(
        '#type'          => 'textarea',
        '#title'         => t('Explanation or submission guidelines'),
        '#description'   => t('This text will be displayed at the top of the page when creating or editing question of this type.'),
        '#default_value' => $question_type->help,
    );

    // Machine-readable type name.
    $form['type'] = array(
        '#type'          => 'machine_name',
        '#default_value' => isset($question_type->type) ? $question_type->type : '',
        '#maxlength'     => 32,
        '#disabled'      => $question_type->isLocked() && $op !== 'clone',
        '#machine_name'  => array('exists' => 'quiz_question_type_load', 'source' => array('label')),
        '#description'   => t('A unique machine-readable name for this question type. It must only contain lowercase letters, numbers, and underscores.'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save question type'), '#weight' => 40);

    if (!$question_type->isLocked() && $op !== 'add' && $op !== 'clone') {
      $form['actions']['delete'] = array(
          '#type'                    => 'submit',
          '#value'                   => t('Delete question type'),
          '#weight'                  => 45,
          '#limit_validation_errors' => array(),
          '#submit'                  => array('quiz_question_type_form_submit_delete')
      );
    }

    return $form;
  }

  public function submit($form, &$form_state) {
    $question_type = entity_ui_form_submit_build_entity($form, $form_state);
    $question_type->description = filter_xss_admin($question_type->description);
    $question_type->help = filter_xss_admin($question_type->help);
    $question_type->save();
    $form_state['redirect'] = 'admin/structure/quiz-questions';
  }

}
