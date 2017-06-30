<?php

namespace corwatts\MarkdownFiles;

use yii;
use \yii\helpers\FileHelper;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface {
  public $posts;
  public $drafts;

  public $file_regex = '/([[:digit:]]{4})-([[:digit:]]{2})-([[:digit:]]{2})_([0-9a-z_]*).md$/i';

  public $files;
  public $results;

  public function bootstrap($app) {
    if ($app instanceof \yii\console\Application) {
      $app->controllerMap[$this->id] = [
        'class' => 'corwatts\MarkdownFiles\commands\BlogController',
        'module' => $this,
      ];
    }
  }

  public function create($title) {
    $filename = date('Y-m-d').'_'.$title.'.md';
    if($this->parseName($filename)) {
      if($path = $this->getPath("$this->posts/$filename")) {
        FileHelper::createDirectory(dirname($path), 0775, true);
        $fhandle = fopen($path, 'wb');
        if($fhandle === false) throw Exception('Cannot create file: '.$path);

        $contents = <<<HEREDOC
---------
author: "Your Name"
title: "Blog Title"
---------

A post
HEREDOC;
        fwrite($fhandle, $contents);
        fclose($fhandle);
        return $path;
      }
    }
    return false;
  }

  public function fetch($params = ['recursive'=>false, 'only'=> ['*.md']]) {
    $files = FileHelper::findFiles($this->getPath($this->posts), $params);
    if(defined('YII_ENV') && (YII_ENV==='dev' || YII_ENV==='test')) {
      $files = array_merge($files, FileHelper::findFiles($this->getPath($this->drafts), $params));
    }
    $this->files = $files;
    return $this;
  }

  public function parse() {
    $parser = new \Hyn\Frontmatter\Parser(new \cebe\markdown\Markdown);
    $parser->setFrontmatter(\Hyn\Frontmatter\Frontmatters\YamlFrontmatter::class);

    $posts = [];
    foreach($this->files as $file) {
      $date = $this->parseName($file);
      if($date) {
        $parsed = $parser->parse(file_get_contents($file));
        array_push($posts, [
          'date'    => $date,
          'yaml'    => $parsed['meta'],
          'content' => $parsed['html'],
        ]);
      }
    }

    $posts = $this->sort($posts);
    $this->results = $posts;
    return $this;
  }

  public function parseName($filepath) {
    if(preg_match($this->file_regex, $filepath, $matches)) {
      return [
        'year'  => $matches[1],
        'month' => $matches[2],
        'day'   => $matches[3],
        'full'  => implode("-", array_slice($matches, 1, 3)),
        'name'  => $matches[4]
      ];
    }
    return false;
  }

  public function getPath($path) {
    if(!is_string($path)) throw new \InvalidArgumentException('getPath only accepts a String. $path was: '.$path);
    return FileHelper::normalizePath(Yii::getAlias($path));
  }

  public function sort($arr) {
    usort($arr, function($a, $b) {
      return $b['date']['full'] <=> $a['date']['full'];
    });
    return $arr;
  }
}