<?php

namespace Drupal\quiz_question\Entity;

use EntityDefaultMetadataController;

class QuestionMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();

    // Define extra metadata info
    // …

    return $info;
  }

}
