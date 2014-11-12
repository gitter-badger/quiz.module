<?php

namespace Drupal\quiz_question\Entity;

use EntityAPIControllerExportable;

class QuestionTypeController extends EntityAPIControllerExportable {

  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $return = parent::save($entity, $transaction);
    $this->addBodyField($entity->type);
    return $return;
  }

  /**
   * Add default body field to a quiz type
   */
  private function addBodyField($bundle) {
    if (!field_info_field('quiz_question_body')) {
      field_create_field(array(
          'field_name'   => 'quiz_question_body',
          'type'         => 'text_with_summary',
          'entity_types' => array('quiz_question'),
      ));
    }

    if (!$instance = field_info_instance('quiz_question', 'quiz_question_body', $bundle)) {
      $instance = field_create_instance(array(
          'field_name'  => 'quiz_question_body',
          'entity_type' => 'quiz_question',
          'bundle'      => $bundle,
          'label'       => t('Question'),
          'widget'      => array('type' => 'text_textarea_with_summary'),
          'settings'    => array('display_summary' => TRUE),
          'display'     => array(
              'default' => array('label' => 'hidden', 'type' => 'text_default'),
          ),
      ));
    }

    return $instance;
  }

}
