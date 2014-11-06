<?php

namespace Drupal\quiz\Entity\Result;

use Drupal\quiz\Entity\QuizEntity;
use stdClass;

class ScoreCalculator {

  /**
   * Calculates the score user received on quiz.
   *
   * @param $quiz
   *   The quiz entity.
   * @param $result_id
   *   Quiz result ID.
   *
   * @return array
   *   Contains three elements: question_count, num_correct and percentage_score.
   */
  public function calculate(QuizEntity $quiz, $result_id) {
    // 1. Fetch all questions and their max scores
    $questions = db_query('SELECT a.question_nid, a.question_vid, n.type, r.max_score
      FROM {quiz_results_answers} a
      LEFT JOIN {node} n ON (a.question_nid = n.nid)
      LEFT OUTER JOIN {quiz_relationship} r ON (r.question_vid = a.question_vid) AND r.quiz_vid = :vid
      WHERE result_id = :rid', array(':vid' => $quiz->vid, ':rid' => $result_id));

    // 2. Callback into the modules and let them do the scoring. @todo after 4.0: Why isn't the scores already saved? They should be
    // Fetched from the db, not calculated…
    $scores = array();
    $count = 0;
    foreach ($questions as $question) {
      // Questions picked from term id's won't be found in the quiz_relationship table
      if ($question->max_score === NULL && isset($quiz->tid) && $quiz->tid > 0) {
        $question->max_score = $quiz->max_score_for_random;
      }

      // Invoke hook_quiz_question_score().
      // We don't use module_invoke() because (1) we don't necessarily want to wed
      // quiz type to module, and (2) this is more efficient (no NULL checks).
      $mod = quiz_question_module_for_type($question->type);
      if (!$mod) {
        continue;
      }

      $function = $mod . '_quiz_question_score';
      if (function_exists($function)) {
        // Allow for max score to be considered.
        $scores[] = $function($quiz, $question->question_nid, $question->question_vid, $result_id);
      }
      else {
        drupal_set_message(t('A @quiz question could not be scored: No scoring info is available', array('@quiz' => QUIZ_NAME)), 'error');
        $dummy_score = new stdClass();
        $dummy_score->possible = 0;
        $dummy_score->attained = 0;
        $scores[] = $dummy_score;
      }
      ++$count;
    }

    // 3. Sum the results.
    $possible_score = 0;
    $total_score = 0;
    $is_evaluated = TRUE;
    foreach ($scores as $score) {
      $possible_score += $score->possible;
      $total_score += $score->attained;
      if (isset($score->is_evaluated)) {
        // Flag the entire quiz if one question has not been evaluated.
        $is_evaluated &= $score->is_evaluated;
      }
    }

    // 4. Return the score.
    return array(
        'question_count'   => $count,
        'possible_score'   => $possible_score,
        'numeric_score'    => $total_score,
        'percentage_score' => ($possible_score == 0) ? 0 : round(($total_score * 100) / $possible_score),
        'is_evaluated'     => $is_evaluated,
    );
  }

}
