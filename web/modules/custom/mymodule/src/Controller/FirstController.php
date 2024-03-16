<?php

namespace Drupal\mymodule\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 *
 */
class FirstController extends ControllerBase {

  /**
   *
   */
  public function simpleContent() {
    return [
      '#type' => 'markup',
      '#markup' => '<h2>' . $this->t('Hello Drupal world. Time flies like an arrow, fruit flies like a banana.') . '</h2>',
    ];
  }

  /**
   *
   */
  public function variableContent($name_1, $name_2) {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('@name1 and @name2 say hello to you', ['@name1' => $name_1, '@name2' => $name_2]),
    ];
  }

}
