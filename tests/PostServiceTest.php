<?php

namespace Tato\Test;

use Tato\Services\PostService;
use Tato\Models\Post;

class PostServiceTest extends BaseTest
{
    /** @var  PostService */
    protected $postService;

    public function setUp()
    {
        parent::setUp();
        $this->postService = new PostService();
    }

    /**
     * @expectedException \Tato\Exceptions\NewPostException
     * @expectedExceptionMessageRegExp /Post title too short : \"(.*)\"/
     */
    public function testPostServiceNewPostBlank()
    {
        $this->postService->newPost("", "");
    }

    /**
     * @expectedException \Tato\Exceptions\NewPostException
     * @expectedExceptionMessageRegExp /Post body too short : \"(.*)\"/
     */
    public function testPostServiceNewPostNobody()
    {
        $this->postService->newPost("Test", "");
    }

    public function testPostServiceNewPostValid()
    {
        $postArray = array();
        for ($i = 1; $i < 4; $i++) {
            //$postArray[] = $this->postService->newPost("Test Title {$i}","Test Body {$i}");
        }
        //$this->assertEquals(3,count($postArray));
    }
}
