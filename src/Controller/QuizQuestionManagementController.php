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
    $terms = array();
    $sql_args = array_keys(quiz()->getVocabularies());
    if (empty($sql_args)) {
      return $terms;
    }

    $query = db_select('taxonomy_term_data', 't')
      ->fields('t', array('name', 'tid'))
      ->condition('t.vid', $sql_args, 'IN');
    if (!$all) {
      $query->condition('t.name', '%' . $start . '%', 'LIKE');
    }
    $res = $query->execute();
    // TODO Don't user db_fetch_object
    while ($res_o = $res->fetch()) {
      $terms[$res_o->tid] = $res_o->name;
    }

    return $terms;
  }

}
