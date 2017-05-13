<?php

namespace corwatts\flat;

use yii;
use \yii\helpers\FileHelper;

class Blog {
  public $posts = '@site/views/blog/posts';
  public $drafts = '@site/views/blog/_drafts';

  public $file_regex = '/([[:digit:]]{4})-([[:digit:]]{2})-([[:digit:]]{2})_([a-z_]*).md$/i';

  public function create($title) {
    $filename = date('Y-m-d').'_'.$title.'.md';
    if($this->parseName($filename)) {
      if($path = Yii::getAlias("$this->posts/$filename")) {
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

  public function getMarkdownFiles() {
    $params = ['recursive'=>false, 'only'=> ['*.md']];
    try {
      $files = FileHelper::findFiles(Yii::getAlias($this->posts), $params);
      if(defined('YII_ENV') && YII_ENV==='dev') {
        $files = array_merge($files, FileHelper::findFiles(Yii::getAlias($this->drafts), $params));
      }
    } catch (Exception $e) {
      $this->stdout($e->message);
    }
    rsort($files); // reverse sort these
    return $files;
  }

  public function parseFiles($files) {
      $parser = new \Hyn\Frontmatter\Parser(new \cebe\markdown\Markdown);
      $parser->setFrontmatter(\Hyn\Frontmatter\Frontmatters\YamlFrontmatter::class);

      $posts = [];
      foreach($files as $file) {
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
      return $posts;
  }

  public function parseName($filepath) {
    if(preg_match($this->file_regex, $filepath, $matches)) {
      return ['year'  => $matches[1],
              'month' => $matches[2],
              'day'   => $matches[3],
              'name'  => $matches[4]];
    }
    return false;
  }
}
