<?php

namespace Drupal\quiz_question\Form;

use Drupal\quiz_question\Entity\Question;

class QuestionForm {

  public function get($form, &$form_state, Question $question, $op) {
    $form = $question->getPlugin()->getEntityForm();

    // @TODO: This is just a testing code, this code should be moved to QuizQuestion
    $form['revision_information'] = array(
        '#type'        => 'fieldset',
        '#title'       => t('Revision information'),
        '#collapsible' => TRUE,
        '#collapsed'   => TRUE,
        '#group'       => 'vtabs',
        '#attributes'  => array('class' => array('node-form-revision-information')),
        '#attached'    => array('js' => array(drupal_get_path('module', 'node') . '/node.js')),
        '#weight'      => 20,
        '#access'      => TRUE,
    );

    $form['revision_information']['revision'] = array(
        '#type'          => 'checkbox',
        '#title'         => t('Create new revision'),
        '#default_value' => FALSE,
        '#state'         => array('checked' => array('textarea[name="log"]' => array('empty' => FALSE))),
    );

    $form['revision_information']['log'] = array(
        '#type'          => 'textarea',
        '#title'         => t('Revision log message'),
        '#row'           => 4,
        '#default_value' => '',
        '#description'   => t('Provide an explanation of the changes you are making. This will help other authors understand your motivations.'),
    );

    return $form;
  }

  public function validate($form, &$form_state) {

  }

  public function submit($form, &$form_state) {

  }

}
