<?php

namespace Drupal\quiz\Helper\Quiz;

use Drupal\quiz\Entity\QuizEntity;
use Drupal\quiz\Entity\Result;
use RuntimeException;
use ShortAnswerQuestion;

class Generator {

  /** @var string[] */
  private $quiz_types;

  /** @var string[] */
  private $question_types;

  /** @var int Maximum number of quizzes per type. */
  private $quiz_limit;

  /** @var int Maximum number of questions per quiz. */
  private $question_limit;

  /** @var int Maximum number of results per quiz. */
  private $result_limit;

  public function __construct($quiz_types, $question_types, $quiz_limit, $question_limit, $result_limit) {
    module_load_include('inc', 'devel_generate', 'devel_generate.fields');
    module_load_include('inc', 'devel_generate', 'devel_generate');

    $this->quiz_types = $quiz_types;
    $this->question_types = $question_types;
    $this->quiz_limit = $quiz_limit;
    $this->question_limit = $question_limit;
    $this->result_limit = $result_limit;
  }

  public function generate() {
    foreach ($this->quiz_types as $quiz_type) {
      $limit = rand(1, $this->quiz_limit);
      for ($i = 0; $i < $limit; ++$i) {
        $this->doGenerate($quiz_type);
      }
    }
  }

  private function doGenerate($quiz_type) {
    /* @var $quiz QuizEntity */
    $quiz = entity_create('quiz_entity', array(
        'type'    => $quiz_type,
        'title'   => devel_create_greeking(rand(5, 10), TRUE),
        'uid'     => rand(0, 1),
        'created' => REQUEST_TIME,
        'changed' => REQUEST_TIME,
    ));
    $quiz->save();

    $limit = rand(1, $this->question_limit);
    for ($i = 0; $i < $limit; ++$i) {
      $this->doGenerateQuestions($quiz, array_rand($this->question_types));
    }

    for ($i = 0; $i < $this->result_limit; ++$i) {
      $this->doGenerateResults($quiz);
    }

    drupal_set_message('Geneated quiz: ' . l($quiz->title, 'quiz/' . $quiz->qid));
  }

  private function doGenerateQuestions(QuizEntity $quiz, $question_type) {
    $question_array = array(
        'type'      => $question_type,
        'comment'   => 2,
        'changed'   => REQUEST_TIME,
        'moderate'  => 0,
        'promote'   => 0,
        'revision'  => 1,
        'log'       => '',
        'status'    => 1,
        'sticky'    => 0,
        'revisions' => NULL,
        'language'  => LANGUAGE_NONE,
        'title'     => devel_create_greeking(rand(5, 20), TRUE),
        'body'      => array(LANGUAGE_NONE => array(array('value' => devel_create_para(rand(20, 50), 1)))),
    );

    #kpr(quiz_question_type_load($question_type)->plugin);
    #exit;

    switch (quiz_question_type_load($question_type)->plugin) {
      case 'truefalse':
        $question_array += $this->dummyTrueFalseQuestion();
        break;
      case 'short_answer':
        $question_array +=$this->dummyShortAnswerQuestion();
        break;
      case 'long_answer':
        $question_array += $this->dummyLongAnswerQuestion();
        break;
      case 'multichoice':
        $question_array += $this->dummyMultichoiceQuestion();
        break;
      case 'quiz_directions':
      case 'quiz_page':
        break;
      default:
        throw new RuntimeException('Unsupported question: ' . quiz_question_type_load($question_type)->plugin);
    }

    /* @var $question \Drupal\quiz_question\Entity\Question */
    $question = entity_create('quiz_question', $question_array);
    $question->save();
    $question->getPlugin()->saveRelationships($quiz->qid, $quiz->vid);
    devel_generate_fields($question, 'quiz_question', $question->type);
  }

  private function dummyTrueFalseQuestion() {
    return array('correct_answer' => rand(0, 1));
  }

  private function dummyShortAnswerQuestion() {
    return array(
        'correct_answer_evaluation' => rand(ShortAnswerQuestion::ANSWER_MATCH, ShortAnswerQuestion::ANSWER_MANUAL),
        'correct_answer'            => devel_create_greeking(rand(10, 20)),
    );
  }

  private function dummyLongAnswerQuestion() {
    return array(
        'rubric' => devel_create_greeking(rand(10, 20))
    );
  }

  private function dummyMultichoiceQuestion() {
    $array = array(
        'choice_multi'   => array_rand(array(0, 1)),
        'choice_random'  => array_rand(array(0, 1)),
        'choice_boolean' => array_rand(array(0, 1)),
    );

    $rand = $array['choice_multi'] ? 1 : rand(2, 10);
    for ($i = 0; $i < $rand; ++$i) {
      $array['alternatives'][] = array(
          'answer'                 => array(
              'value'  => devel_create_greeking(rand(2, 10)),
              'format' => 'filtered_html',
          ),
          'feedback_if_chosen'     => array(
              'value'  => devel_create_greeking(rand(5, 10)),
              'format' => 'filtered_html',
          ),
          'feedback_if_not_chosen' => array(
              'value'  => devel_create_greeking(rand(5, 10)),
              'format' => 'filtered_html',
          ),
          'score_if_chosen'        => 1,
          'score_if_not_chosen'    => 0,
      );
    }

    return $array;
  }

  private function doGenerateResults(QuizEntity $quiz) {
    /* @var $result Result */
    $result = entity_create('quiz_result', array(
        'quiz_qid'     => $quiz->qid,
        'quiz_vid'     => $quiz->vid,
        'uid'          => rand(0, 1),
        'time_start'   => REQUEST_TIME,
        'time_end'     => REQUEST_TIME + rand(15, 300),
        'released'     => '???',
        'score'        => '???',
        'is_invalid'   => FALSE,
        'is_evaluated' => '???',
        'time_left'    => 0,
    ));
    $result->save();
  }

}
