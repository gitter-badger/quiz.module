<?php

namespace Drupal\quiz\Controller\Admin;

use Drupal\quiz\Entity\QuizEntity;

class QuizQuestionAdminController {

  private $quiz;

  public function __construct(QuizEntity $quiz) {
    $this->quiz = $quiz;
  }

  public function getQuestionAddingLinks() {
    $items = array();

    foreach (quiz_get_question_types() as $type => $info) {
      if (!node_access('create', $type)) {
        continue;
      }

      $text = $info['name'];
      $url = 'node/add/' . str_replace('_', '-', $type);
      $items[] = l($text, $url, array('query' => array(
            'quiz_qid' => $this->quiz->qid,
            'quiz_vid' => $this->quiz->vid,
          ) + drupal_get_destination()));
    }

    if (empty($items)) {
      $items[] = t('You have not enabled any question type module or no has permission been given to create any question.');
    }

    return $items;
  }

}
