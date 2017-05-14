<?php

namespace corwatts\MarkdownFiles\commands;

use \yii\helpers\BaseConsole as Console;

/**
 * Creates a new blog post
 */
class BlogController extends \yii\console\Controller {
  public $defaultAction = 'create';
  public $title;

  /**
   * Creates a new blog post
   */
  public function actionCreate($title) {
    $path = $this->module->create($title);
    if(!$path) {
      $this->stdout("Unable to create file", Console::FG_RED);
      return 1;
    }

    $this->stdout("The file ", Console::FG_GREEN);
    $this->stdout($path, Console::FG_GREEN, Console::BOLD);
    $this->stdout(" has been generated.", Console::FG_GREEN);
    return 0;
  }

  public function options($actionID) {
    return array_merge(
      parent::options($actionID),
      ['title'] // global for all actions
    );
  }

  /*
   * We're overriding this from the BaseController
   * so that this controller + actions show up correctly
   * in the console when we run ```./yii```
   */
  public function getUniqueId() {
    return $this->id;
  }
}