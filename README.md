# Yii2 Markdown Files

![Build Status](https://github.com/corwatts/yii2-markdown-files/actions/workflows/actions.yml/badge.svg)
[![codecov](https://codecov.io/gh/CorWatts/yii2-markdown-files/branch/master/graph/badge.svg)](https://codecov.io/gh/CorWatts/yii2-markdown-files)

yii2-markdown-files provides a simple way to write updates, posts, or blocks of text in individual Markdown files with YAML frontmatter, render them on the fly and use the rendered HTML and frontmatter however you like.

Give updates on your Yii2 site or have a list of posts for a simple blog or news feed. Store your posts in version control with the rest of your code. **No database required!**

The idea for this extension was influenced by Jekyll, a static website generator written in Ruby.

## Installation
Install via composer:  
```bash
composer require 'corwatts/yii2-markdown-files'
```

## Configuration
Enable the module by adding the snippet below to your main.php configuration file. 

```php
'modules' => [
  'blog' => [ // name this module what you like
    'class' => \corwatts\MarkdownFiles\Module::className(),
    'posts' => '@frontend/views/blog/posts',
    'drafts' => '@frontend/views/blog/drafts',
  ]
],
```
- `class`: is the namespaced class for this module  
- `posts`: is a path pointing to the directory containing publishable markdown files. The path can contain Yii2 aliases.  
- `drafts`: is a path pointing to the directory containing markdown files that aren't quite ready for publishing. The path can contain Yii2 aliases. **Drafts are only rendered in the Yii2 `dev` environment.**

**Note:** If you're going to use the included console command ensure this configuration is added somewhere the console application can access (like `common/config/main.php`).

## Usage
Before rendering and displaying posts the individual post files must be created. A simple way to scaffold new posts is using the console command included in this extension. See below for instructions on how to set it up and use it.

It is easy to create new posts _without_ the included console command. Posts follow a specific ruleset:  

- Create a file in the `posts` or `drafts` directory path specified in the module configuration above.  
-  Similar to Jekyll, the filename has a specific format. It should start with the date (YYYY-MM-DD format) followed by a snake_cased description, and ending with the `.md` extension. Something like `2017-05-20_test_post_1.md`. When these files are processed the date is extracted from the filename. The rest of the descriptive filename is not used at this time.

### Post Template
A basic post template looks like this:

```markdown
---------
author: "Your Name"
title: "Blog Title"
---------

A post
```

The default YAML variables are `author` and `title`. You can set your own custom variables here too, and they will be available on the rendered side.

After running files through `parseFiles()` you'll have an array of arrays that look like this:

```php
[
  [
    'date'   => [
      'year'   => '2017',
      'month'  => '05',
      'day'    => '12',
      'full'   => '2017-05-12',
      'name'   => 'hello_world'
    ],
    'yaml'   => [
      'title'  => 'Blog Title',
      'author' => 'Your Name'
    ],
    'content' => '<p>A post</p>'
  ],
  ...[other posts]...
]
```

You can then iterate over the arrays and render the posts in your view partial.

```php
$posts = \Yii::$app->getModule('blog'); // get module instance
                   ->fetch()    // get a list of markdown files
                   ->parse()    // parse the list of files
                   ->result;    // grab the array of parsed files
return $this->render('index', ['posts'=>$posts]); //render the view
```

_Protip:_ Cache the results to avoid having to recompile all your posts on every page
hit.

Now you can render this data in a simple partial like this:

```php
<?php
use \yii\helpers\Html;

foreach($posts as $file) {
  $yaml    = $file['yaml'];
  $content = $file['content'];

  $date = Html::encode(date('F j, Y', strtotime($file['date']['full'])));
  print "<h4>".Html::encode($yaml['title'])."</h4>";
  print "<em>Written by ".Html::encode($yaml['author'])." on $date</em>";
  print $content;
}
```


## Console Command
Yii2-markdown-files provides a console command that plugs into the `./yii` command line tool. You can use it to easily generate new posts. By default it provdes the 'blog/create' command.

To enable this, bootstrap the BlogController by adding it to the bootstrap array in your console configuration. Likely `console/config/main.php`:

```php
'bootstrap' => ['blog'],
```

Now you can execute `./yii blog/create hello_world`. Provide a descriptive snake_cased slug like `my_first_post`. It will generate a post template and print out its location. Edit this template to write your post.


### Testing
Unit tests are included with this codebase. First scaffold the testing framework via `composer test-scaffold` (this only needs to be ran the first time). The unit tests can then be ran via `composer test` and coverage can be generated via `composer test-coverage`.
