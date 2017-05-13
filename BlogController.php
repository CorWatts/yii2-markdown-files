<?php

namespace corwatts\flat;

use \yii\helpers\BaseConsole as Console;

class BlogController extends \yii\console\Controller {

  public function actionCreate($title) {
    $blog = new Blog();
    $path = $blog->create($title);
    if(!$path) {
      $this->stdout("Unable to create file", Console::FG_RED);
      return 1;
    }

    $this->stdout("The file ", Console::FG_GREEN);
    $this->stdout($path, Console::FG_GREEN, Console::BOLD);
    $this->stdout(" has been generated.", Console::FG_GREEN);
    return 0;
  }
}