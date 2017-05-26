<?php

namespace corwatts\tests;

use yii;
use Codeception\Specify;
use \corwatts\MarkdownFiles\Module;
use \InvalidArgumentException;

class BlogTest extends \Codeception\Test\Unit {
  use Specify;

  public function testParseName() {
    $blog = new Module('blog');

    expect('parseName() should parse a good filepath into the expected components', $this->assertEquals($blog->parseName('2017-06-13_a_VERY_good_post.md'),
    ['year'  => '2017',
     'month' => '06',
     'day'   => '13',
     'full'  => '2017-06-13',
     'name'  => 'a_VERY_good_post']));

    expect('parseName should parse a whole filepath with the filename at the end into the expected components', $this->assertEquals($blog->parseName('/var/www/vhosts/coolsite.com/blog/2017-06-13_a_VERY_good_post.md'),
    ['year'  => '2017',
     'month' => '06',
     'day'   => '13',
     'full'  => '2017-06-13',
     'name'  => 'a_VERY_good_post']));

    expect('parseName should return false for an unparseable filepath', $this->assertFalse($blog->parseName('NOTWHATWEEXPECTED')));

    expect('parseName should return false for a substring that matches the regex but is NOT at the end of the string', $this->assertFalse($blog->parseName('/root/2017-06-13_a_VERY_good_post.md/blarg.zip')));

    expect("parseName should return false for a string that doesn't quite match the correct pattern", $this->assertFalse($blog->parseName('2017-6-13_a_VERY_good_post.md'))); // the MONTH only has one digit instead of the expected two
  }

  public function testCreate() {
    $blog = new Module('blog');
    // put these in the tests _output directory
    $blog->posts = dirname(__DIR__)."/_output/posts";

    expect('create should accept a title parameter and create a file at the  post path', $this->assertFileExists($blog->create('test_post_1')));
    expect('create should accept refuse to create a malformed filename', $this->assertFalse($blog->create('bad/name/here')));
  }

  public function testGetMarkdownFiles() {
    $blog = new Module('blog');
    $blog->posts = dirname(__DIR__)."/_data/posts";
    $blog->drafts = dirname(__DIR__)."/_data/drafts";

    expect('getMarkdownFiles should scan the $posts directory and return the markdown files in descending order', $this->assertEquals([
      dirname(__DIR__).'/_data/posts/2017-05-20_test_post_3.md',
      dirname(__DIR__).'/_data/posts/2017-05-20_test_post_2.md',
      dirname(__DIR__).'/_data/posts/2017-05-20_test_post_1.md',
      ], $blog->getMarkdownFiles()));

    //expect('getMarkdownFiles should include the $drafts directory when the YII_ENV environment variable is set to "dev"', $this->assertEquals([
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_draft_3.md',
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_draft_2.md',
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_draft_1.md',
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_post_3.md',
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_post_2.md',
    //  dirname(__DIR__).'/_data/posts/2017-05-20_test_post_1.md',
    //  ], $blog->getMarkdownFiles()));
    
    $this->specify('getMarkdownFiles should throw an exception', function() use($blog) {
      $this->expectException('\yii\base\InvalidParamException');
      $this->expectExceptionMessage('The dir argument must be a directory: /bad/path/here/so/throw');
      $blog->posts = '/bad/path/here/so/throw';
      expect('getMarkdownFiles should throw an exception if FileHelper:findFiles() throws', $blog->getMarkdownFiles());
    });
  }

  public function testParseFiles() {
    $blog = new Module('blog');
    $blog->posts = dirname(__DIR__)."/_data/posts";
    $blog->drafts = dirname(__DIR__)."/_data/drafts";

    expect('parseFiles should accept an array of valid markdown files, parse them, and return an array of data for each post', $this->assertEquals($blog->parseFiles($blog->getMarkdownFiles()), [
      [
        'date' => ['year' => '2017', 'month' => '05', 'day' => '20', 'full' => '2017-05-20', 'name' => 'test_post_3'],
        'yaml' => ['author' => 'Your Name', 'title' => 'Blog Title'],
        'content' => "<p>A post</p>\n",
      ], [
        'date' => ['year' => '2017', 'month' => '05', 'day' => '20', 'full' => '2017-05-20', 'name' => 'test_post_2'],
        'yaml' => ['author' => 'Your Name', 'title' => 'Blog Title'],
        'content' => "<p>A post</p>\n",
      ], [
        'date' => ['year' => '2017', 'month' => '05', 'day' => '20', 'full' => '2017-05-20', 'name' => 'test_post_1'],
        'yaml' => ['author' => 'Your Name', 'title' => 'Blog Title'],
        'content' => "<p>A post</p>\n",
      ],
    ]));
  }

  public function testGetPath() {
    $blog = new Module('blog');
    $blog->posts = '@app/_data/posts';
    expect('getPath should return a properly dealiased and normalized path', $this->assertEquals(dirname(__DIR__).'/_data/posts', $blog->getPath($blog->posts)));

    $blog->posts = '@app//_data\posts';
    expect('getPath should return a properly dealiased and normalized path when given something a bit off', $this->assertEquals(dirname(__DIR__).'/_data/posts', $blog->getPath($blog->posts)));

    $this->specify('getPath should throw an exception if not given a string', function() use($blog) {
      $this->expectException("\InvalidArgumentException");
      expect('getPath should only accept a string',  $blog->getPath(123));
    });
  }
}

