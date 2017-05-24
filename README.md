# Yii2 Markdown Files

[![Build Status](https://travis-ci.org/CorWatts/yii2-markdown-files.svg?branch=master)](https://travis-ci.org/CorWatts/yii2-markdown-files)
[![codecov](https://codecov.io/gh/CorWatts/yii2-markdown-files/branch/master/graph/badge.svg)](https://codecov.io/gh/CorWatts/yii2-markdown-files)

yii2-markdown-files provides a simple way to write updates, posts, or blocks of text in individual Markdown files with YAML frontmatter, render them on the fly and use the rendered HTML and frontmatter however you like. Commonly this is used to put the rendered text into a partial or view file.

Give updates on your Yii2 site or have a list of posts for a simple blog or news feed. Store your posts in version control with the rest of your code. **No database required!**

_Note: The idea for this was influenced by Jekyll, a static website generator written in Ruby._

## Installation
Install via composer a la:  
```bash
composer require 'corwatts/yii2-markdown-files'
```

## Configuration
Enable the module by adding the snippet below to your main.php configuration file. 

```php
'modules' => [
  'blog' => [ // name this module what you like
    'class' => 'corwatts\MarkdownFiles\Module',
    'posts' => '@frontend/views/blog/posts',
    'drafts' => '@frontend/views/blog/drafts',
  ]
],
```
`class`: is the namespaced class for this module
`posts`: is a path pointing to the directory containing publishable markdown files. The path can contain Yii2 aliases.  
`drafts`: is a path pointing to the directory containing markdown files that aren't quite ready for publishing. The path can contain Yii2 aliases.  

**Note:** If you're going to use the included console command ensure this configuration is added somewhere the console application can access (like `common/config/main.php`).

## Usage
```php
$blog = \Yii::$app->getModule('blog'); // get module instance
$files = $blog->getMarkdownFiles();    // get a list of markdown files
$posts = $blog->parseFiles($files);    // parse the list of files
return $this->render('index', ['posts'=>$posts]); //render our view
```

You can render this data in a simple partial like this:

```html
<?php
use \yii\helpers\Html;

foreach($posts as $file) {
  $date      = $file['date'];
  $yaml      = $file['yaml'];
  $content   = $file['content'];
  $this_date = date('F j, Y', strtotime($date['year']."-".$date['month']."-".$date['day']));
  
  print "<h4>".Html::encode($yaml['title'])."</h4>";
  print "<em>Written by ".Html::encode($yaml['author'])." on ".Html::encode($this_date)."</em>";
  print $content;
}
```

A basic post template looks like this:
```
---------
author: "Your Name"
title: "Blog Title"
---------

A post
```

The default YAML variables are `author` and `title`. You can set your own custom variables here too, and they will be available on the rendered side.

After running files through `parseFiles()` you'll have an array of arrays that look like this:
```
[
  [
    'date'   => [
      'year'   => '2017',
      'month'  => '05',
      'day'    => '12',
      'name'   => 'hello_world'
    ],
    'yaml'   => [
      'title'  => 'Blog Title',
      'author' => 'Your Name'
    ],
    'content' => <p>A post</p>'
  ],
  ...
]
```

You can then iterate over the arrays and render the posts in your view partial.



## Console Command
Yii2-markdown-files provides a console command that plugs into the `./yii` command line tool. You can use it to easily generate new posts. By default it provdes the 'blog/create' command.

To enable this, bootstrap the BlogController by adding it to the bootstrap array in your console configuration. Likely `console/config/main.php`:

```php
'bootstrap' => ['blog'],
```

Now you can execute `./yii blog/create hello_world`. Provide a descriptive snakecased slug like `my_first_post`. It will generate a post template and print out its location. Edit this template to write your post.
