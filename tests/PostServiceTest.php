<?php

namespace Tato\Test;

use Tato\Models\User;
use Tato\Services\PostService;
use Tato\Services\TestSessionService;
use Tato\Models\Post;

class PostServiceTest extends BaseTest
{
    /** @var  PostService */
    protected $postService;

    /** @var  User */
    protected $fakeUser;
    /** @var  User */
    protected $fakeUser2;
    /** @var  TestSessionService */
    protected $sessionService;

    public function setUp()
    {
        parent::setUp();
        $this->sessionService = new TestSessionService();

        $this->fakeUser = new User();
        $this->fakeUser2 = new User();

        $this->postService = new PostService($this->sessionService);
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
