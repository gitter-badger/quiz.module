<?php

namespace Drupal\quiz\Helper;

use Drupal\quiz\Helper\Node\NodeDeleteHelper;
use Drupal\quiz\Helper\Node\NodeFormHelper;
use Drupal\quiz\Helper\Node\NodeInsertHelper;
use Drupal\quiz\Helper\Node\NodePresaveHelper;
use Drupal\quiz\Helper\Node\NodeUpdateHelper;
use Drupal\quiz\Helper\Node\NodeValidateHelper;
use Drupal\quiz\Helper\Node\NodeViewHelper;

class NodeHelper {

  private $nodeDeleteHelper;
  private $nodeValidateHelper;
  private $nodeInsertHelper;
  private $nodeUpdateHelper;
  private $nodePresaveHelper;
  private $nodeViewHelper;
  private $nodeFormHelper;

  public function getNodeDeleteHelper() {
    if (null === $this->nodeDeleteHelper) {
      $this->nodeDeleteHelper = new NodeDeleteHelper();
    }
    return $this->nodeDeleteHelper;
  }

  public function setNodeDeleteHelper($helper) {
    $this->nodeDeleteHelper = $helper;
    return $this;
  }

  public function getNodeValidateHelper() {
    if (null === $this->nodeValidateHelper) {
      $this->nodeValidateHelper = new NodeValidateHelper();
    }
    return $this->nodeValidateHelper;
  }

  public function setNodeValidateHelper($nodeValidateHelper) {
    $this->nodeValidateHelper = $nodeValidateHelper;
    return $this;
  }

  public function getNodeInsertHelper() {
    if (null === $this->nodeInsertHelper) {
      $this->nodeInsertHelper = new NodeInsertHelper();
    }
    return $this->nodeInsertHelper;
  }

  public function setNodeInsertHelper($nodeInsertHelper) {
    $this->nodeInsertHelper = $nodeInsertHelper;
    return $this;
  }

  public function getNodeUpdateHelper() {
    if (null === $this->nodeUpdateHelper) {
      $this->nodeUpdateHelper = new NodeUpdateHelper();
    }
    return $this->nodeUpdateHelper;
  }

  public function setNodeUpdateHelper($nodeUpdateHelper) {
    $this->nodeUpdateHelper = $nodeUpdateHelper;
    return $this;
  }

  public function getNodePresaveHelper() {
    if (null === $this->nodePresaveHelper) {
      $this->nodePresaveHelper = new NodePresaveHelper();
    }
    return $this->nodePresaveHelper;
  }

  public function setNodePresaveHelper($nodePresaveHelper) {
    $this->nodePresaveHelper = $nodePresaveHelper;
    return $this;
  }

  public function getNodeViewHelper() {
    if (null === $this->nodeViewHelper) {
      $this->nodeViewHelper = new NodeViewHelper();
    }
    return $this->nodeViewHelper;
  }

  public function setNodeViewHelper($nodeViewHelper) {
    $this->nodeViewHelper = $nodeViewHelper;
    return $this;
  }

  public function delete($node) {
    $this->getNodeDeleteHelper()->execute($node);
  }

  public function validate($node) {
    $this->getNodeValidateHelper()->execute($node);
  }

  public function insert($node) {
    $this->getNodeInsertHelper()->execute($node);
  }

  public function update($node) {
    $this->getNodeUpdateHelper()->execute($node);
  }

  public function presave($node) {
    $this->getNodePresaveHelper()->execute($node);
  }

  public function view($node, $view_mode) {
    return $this->getNodeViewHelper()->execute($node, $view_mode);
  }

  /**
   * @return NodeFormHelper
   */
  public function getNodeFormHelper() {
    if (null === $this->nodeFormHelper) {
      $this->nodeFormHelper = new NodeFormHelper();
    }
    return $this->nodeFormHelper;
  }

  public function setNodeFormHelper($nodeFormHelper) {
    $this->nodeFormHelper = $nodeFormHelper;
    return $this;
  }

}