<?php

namespace Tato\Test;

use Tato\Exceptions\NewCommentException;
use Tato\Models\Comment;
use Tato\Models\User;
use Tato\Services\CommentService;
use Tato\Services\PostService;
use Tato\Models\Post;
use Tato\Services\UserService;
use Tato\Test\Mocks\CommentServiceTestData;
use Tato\Test\Mocks\TestSessionService;

class CommentServiceTest extends BaseTest
{
    /** @var PostService */
    protected $postService;

    /** @var CommentService */
    protected $commentService;

    /** @var  TestSessionService */
    protected $sessionService;

    /** @var  UserService */
    protected $userService;

    protected $password = "Fak3P455**";

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        User::deleteTable();
        Post::deleteTable();
        Comment::deleteTable();
    }
    
    public static function tearDownAfterClass()
    {
        User::deleteTable();
        Post::deleteTable();
        Comment::deleteTable();
    }

    public function setUp()
    {
        parent::setUp();
        $this->sessionService = new TestSessionService();
        $this->sessionService->start();
        $this->userService = new UserService($this->sessionService);

        $this->postService = new PostService($this->sessionService);

        $this->commentService = new CommentService($this->sessionService, $this->postService);
    }

    /**
     * @expectedException \Tato\Exceptions\NewCommentException
     * @expectedExceptionMessage Title and/or body too short
     */
    public function testCommentServiceNewCommentTooShort()
    {
        $this->commentService->newComment(1, "", "");
    }

    /**
     * @expectedException \Tato\Exceptions\NewCommentException
     * @expectedExceptionMessage Invalid Post ID :
     */
    public function testCommentServiceNewCommentInvalidPostID()
    {
        $this->commentService->newComment(-1, "Title", "Body");
    }

    /**
     * @expectedException \Tato\Exceptions\NewCommentException
     * @expectedExceptionMessage Invalid User Session
     */
    public function testCommentServiceNewCommentNoUserSession()
    {
        $this->commentService->newComment(1, "Title", "Body");
    }

    public function testSetupEnvironment()
    {
        $data = new CommentServiceTestData();
        $data->fakeUser1 = $this->userService->newUser(
            $this->faker->safeEmail,
            $this->faker->userName,
            $this->password
        );
        $data->fakeUser2 = $this->userService->newUser(
            $this->faker->safeEmail,
            $this->faker->userName,
            $this->password
        );

        $user = $this->userService->loginUser($data->fakeUser1->name, $this->password);

        $this->assertInstanceOf(User::class, $user);

        $data->postArray = array();
        for ($i = 1; $i < 4; $i++) {
            $data->postArray[] = $this->postService->newPost("Test Post Title {$i}", "Test Post Body {$i}");
        }
        $this->assertEquals(3, count($data->postArray));

        return $data;
    }

    /**
     * @depends testSetupEnvironment
     * @expectedException \Tato\Exceptions\NewCommentException
     * @expectedExceptionMessage No Post With ID : "
     */
    public function testCommentServiceNewCommentNoPost(CommentServiceTestData $data)
    {
        $this->userService->loginUser($data->fakeUser1->name, $this->password);

        $this->commentService->newComment(100, "Test", "Test");
    }

    /**
     * @depends testSetupEnvironment
     */
    public function testCommentServiceNewComment(CommentServiceTestData $data)
    {
        $this->userService->loginUser($data->fakeUser1->name, $this->password);

        $data->commentArray1 = array();
        for ($i = 1; $i < 3; $i++) {
            $data->commentArray1[] = $this->commentService->newComment(
                $data->postArray[0]->post_id,
                "Test Comment Title {$i}",
                "Test Comment Body {$i}"
            );
        }
        for ($i = 1; $i < 3; $i++) {
            $data->commentArray2[] = $this->commentService->newComment(
                $data->postArray[1]->post_id,
                "Test Comment Title {$i}",
                "Test Comment Body {$i}"
            );
        }
        return $data;
    }

    /**
     * @depends testCommentServiceNewComment
     */
    public function testCommentServiceGetByIDInvlidID(CommentServiceTestData $data)
    {
        $this->assertFalse($this->commentService->getByID(-1));
        return $data;
    }

    /**
     * @depends testCommentServiceGetByIDInvlidID
     */
    public function testCommentServiceGetByIDNoSuchID(CommentServiceTestData $data)
    {
        $this->assertFalse($this->commentService->getByID(1000000000));
        return $data;
    }

    /**
     * @depends testCommentServiceGetByIDNoSuchID
     */
    public function testCommentServiceGetByID(CommentServiceTestData $data)
    {
        /** @var $comment Comment */
        foreach ($data->commentArray1 as $comment) {
            $testComment = $this->commentService->getByID($comment->comment_id);
            $this->assertEquals($comment, $testComment);
        }
        foreach ($data->commentArray2 as $comment) {
            $testComment = $this->commentService->getByID($comment->comment_id);
            $this->assertEquals($comment, $testComment);
        }

        return $data;
    }

    /**
     * @depends testCommentServiceGetByID
     */
    public function testCommentServiceGetByPostID(CommentServiceTestData $data)
    {
        $this->assertEquals(2, count(
            $this->commentService->getByPostID($data->postArray[0]->post_id)
        ));
        $this->assertEquals(2, count(
            $this->commentService->getByPostID($data->postArray[1]->post_id)
        ));
    }
}
