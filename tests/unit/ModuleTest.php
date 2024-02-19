<?php

namespace corwatts\tests;

use yii;
use \corwatts\MarkdownFiles\Module;

class ModuleTest extends \Codeception\Test\Unit {

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

  public function testFetch() {
    $blog = new Module('blog');
    $blog->posts = dirname(__DIR__)."/_data/posts";
    $blog->drafts = dirname(__DIR__)."/_data/drafts";

    $expected = [
      dirname(__DIR__).'/_data/posts/2017-05-20_test_post_1.md',
      dirname(__DIR__).'/_data/posts/2017-05-21_test_post_2.md',
      dirname(__DIR__).'/_data/posts/2017-05-22_test_post_3.md',
      dirname(__DIR__).'/_data/drafts/2017-05-23_test_draft_1.md',
      dirname(__DIR__).'/_data/drafts/2017-05-24_test_draft_2.md',
      dirname(__DIR__).'/_data/drafts/2017-05-25_test_draft_3.md',
    ];
    sort($expected, SORT_NATURAL);

    $actual = $blog->fetch()->files;
    sort($actual, SORT_NATURAL);

    expect('fetch should scan the $posts directory and return the markdown files in descending order', $this->assertEquals($expected, $actual));

    // fetch should throw an exception
    $this->tester->expectThrowable(new yii\base\InvalidArgumentException('The dir argument must be a directory: /bad/path/here/so/throw'), function() use($blog) {
      $blog->posts = '/bad/path/here/so/throw';
      $blog->fetch()->files;
    });
  }

  public function testParse() {
    $sorted_data = require(dirname(__DIR__).'/_data/sorted_file_list.php');

    $blog = new Module('blog');
    $blog->posts = dirname(__DIR__)."/_data/posts";
    $blog->drafts = dirname(__DIR__)."/_data/drafts";

    expect('Parse should accept an array of valid markdown files, parse them, and return an array of data for each post', $this->assertEquals($blog->parse($blog->fetch()->files)->results, $sorted_data));
  }

  public function testGetPath() {
    $blog = new Module('blog');
    $blog->posts = '@app/_data/posts';
    expect('getPath should return a properly dealiased and normalized path', $this->assertEquals(dirname(__DIR__).'/_data/posts', $blog->getPath($blog->posts)));

    $blog->posts = '@app//_data\posts';
    expect('getPath should return a properly dealiased and normalized path when given something a bit off', $this->assertEquals(dirname(__DIR__).'/_data/posts', $blog->getPath($blog->posts)));

    // getPath should throw an exception if not given a string
    $this->tester->expectThrowable("\InvalidArgumentException", function() use($blog) {
      // getPath should only accept a string
      $blog->getPath(123);
    });
  }

  public function testSort() {
    $blog = new Module('blog');
    $data        = require(dirname(__DIR__).'/_data/parsed_file_list.php');
    $sorted_data = require(dirname(__DIR__).'/_data/sorted_file_list.php');

    expect('sort should sort an array of parsed files by date descending', $this->assertEquals($blog->sort($data), $sorted_data));
  }
}
