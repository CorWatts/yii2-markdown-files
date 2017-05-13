<?php

namespace common\tests\unit\components;

use Yii;
use Codeception\Specify;
use common\components\Blog;

class BlogTest extends \Codeception\Test\Unit
{
  public function testParseName() {
    $blog = new Blog();

    expect('parseName should parse a good filepath into the expected components', $this->assertEquals($blog->parseName('2017-06-13_a_VERY_good_post.md'),
    ['year'  => '2017',
     'month' => '06',
     'day'   => '13',
     'name' => 'a_VERY_good_post']));

    expect('parseName should return false for an unparseable filepath', $this->assertFalse($blog->parseName('NOTWHATWEEXPECTED')));

    expect('parseName should return false for a substring that matches the regex but is NOT at the end of the string', $this->assertFalse($blog->parseName('/root/2017-06-13_a_VERY_good_post.md/blarg.zip')));

    expect("parseName should return false for a string that doesn't quite match the correct pattern", $this->assertFalse($blog->parseName('2017-6-13_a_VERY_good_post.md'))); // the MONTH only has one digit instead of the expected two
  }
}

