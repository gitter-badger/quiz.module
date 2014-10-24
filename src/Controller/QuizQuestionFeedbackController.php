<?php

namespace Drupal\quiz\Controller;

use Drupal\quiz\Entity\QuizEntity;
class QuizQuestionFeedbackController {

  /** @var QuizEntity */
  private $quiz;

  /** @var int */
  private $quiz_id;

  public function __construct($quiz) {
    $this->quiz = $quiz;
    $this->quiz_id = __quiz_entity_id($this->quiz);
  }

  /**
   * Callback for node/%quiz_menu/take/%question_number/feedback. Show feedback
   * for a question response.
   */
  public static function staticCallback($quiz, $question_number) {
    $controller = new static($quiz);
    return $controller->render($question_number);
  }

  public function render($question_number) {
    if (empty($_SESSION['quiz'][__quiz_entity_id($this->quiz)]['result_id'])) {
      $result_id = $_SESSION['quiz']['temp']['result_id'];
    }
    else {
      $result_id = $_SESSION['quiz'][__quiz_entity_id($this->quiz)]['result_id'];
    }
    $quiz_result = quiz_result_load($result_id);
    $question = node_load($quiz_result->layout[$question_number]['nid']);
    $feedback = $this->buildRenderArray($question);
    return $feedback;
  }

  public function buildRenderArray($question) {
    require_once DRUPAL_ROOT . '/' . drupal_get_path('module', 'quiz') . '/quiz.pages.inc';

    if (empty($_SESSION['quiz'][$this->quiz_id]['result_id'])) {
      $result_id = $_SESSION['quiz']['temp']['result_id'];
    }
    else {
      $result_id = $_SESSION['quiz'][$this->quiz_id]['result_id'];
    }

    $types = _quiz_get_question_types();
    $module = $types[$question->type]['module'];

    // Invoke hook_get_report().
    if ($report = module_invoke($module, 'get_report', $question->nid, $question->vid, $result_id)) {
      $report_form = @drupal_get_form('Drupal\quiz\Form\QuizReportForm::staticCallback', array($report));
      return $report_form;
    }
  }

}
