<?php

namespace Drupal\quiz\Controller;

class QuizQuestionManagementController {

  /**
   * Callback for quiz/%/questions/term_ahah. Ahah function for finding terms…
   *
   * @param string $start
   *  The start of the string we are looking for
   */
  public static function categorizedTermAhah($start) {
    foreach (static::searchTerms($start, $start == '*') as $key => $value) {
      $to_json["$value (id:$key)"] = $value;
    }
    drupal_json_output(!empty($to_json) ? $to_json : array());
  }

  /**
   * Helper function for finding terms...
   *
   * @param string $start
   *  The start of the string we are looking for
   */
  public static function searchTerms($start, $all = FALSE) {
    if (!$sql_args = array_keys(quiz()->getVocabularies())) {
      return array();
    }

    $query = db_select('taxonomy_term_data', 't')
      ->fields('t', array('name', 'tid'))
      ->condition('t.vid', $sql_args);
    if (!$all) {
      $query->condition('t.name', '%' . $start . '%', 'LIKE');
    }
    $result = $query->execute();

    // @TODO: Don't user db_fetch_object
    $terms = array();
    while ($row = $result->fetch()) {
      $terms[$row->tid] = $row->name;
    }

    return $terms;
  }

}
