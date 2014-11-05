<?php

namespace Drupal\quiz\Controller;

use Drupal\quiz\Entity\QuizEntity;

class QuizResultController extends QuizResultBaseController {

  /**
   * Callback for quiz-result/%
   *
   * Quiz result report page for the quiz admin section
   *
   * @param QuizEntity $quiz
   * @param $result_id
   *   The result id
   */
  public static function staticCallback($quiz, $result_id) {
    $result = quiz_result_load($result_id);
    $quiz_revision = quiz_load($quiz->qid, $result->vid);
    $obj = new static($quiz, $quiz_revision, $result);
    return $obj->render();
  }

  public function render() {
    // Get all the data we need.
    // Lets add the quiz title to the breadcrumb array.
    # $breadcrumb = drupal_get_breadcrumb();
    # $breadcrumb[] = l(t('Quiz Results'), 'admin/quiz/reports/results');
    # $breadcrumb[] = l($quiz->title, 'admin/quiz/reports/results/' . __quiz_entity_id($quiz));
    # drupal_set_breadcrumb($breadcrumb);

    $data = array(
        'quiz'      => $this->quiz_revision,
        'questions' => $this->getAnswers(),
        'score'     => $this->score,
        'summary'   => $this->getSummaryText(),
        'result_id' => $this->result->result_id,
        'account'   => user_load($this->result->uid),
    );

    return theme('quiz_result', $data);
  }

}
