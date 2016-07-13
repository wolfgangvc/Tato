<?php
namespace Tato\Services;

use phpDocumentor\Reflection\Types\Boolean;
use Tato\Exceptions\DeletePostException;
use Tato\Exceptions\EditPostException;
use Tato\Exceptions\NewPostException;
use Tato\Models\Post;
use Tato\Models\User;

class PostService
{
    /** @var SessionService */
    protected $sessionService;

    public function __construct($sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function getByID(int $id, $allowDeleted = false)
    {
        $search = Post::search();
        if (!$allowDeleted) {
            $search->where("deleted", "no");
        }
        return $search
            ->where("post_id", $id)
            ->execOne();
    }

    public function getPosts(int $limit = 10, int $offset = 0, $includeDeleted = false)
    {
        $limit = ($limit < 1) ? 1 : $limit;
        $offset = ($offset < 0) ? 0 : $offset;
        $search = Post::search();
        if (!$includeDeleted) {
            $search->where("deleted", "no");
        }
        return $search
            ->limit($limit, $offset)
            ->exec();
    }

    public function getByUserID(int $user_id, int $limit = 10, int $offset = 0, $includeDeleted = false)
    {
        if ($user_id < 1) {
            return false;
        }
        $limit = ($limit < 1) ? 1 : $limit;
        $offset = ($offset < 0) ? 0 : $offset;
        $search = Post::search();
        if (!$includeDeleted) {
            $search->where("deleted", "no");
        }
        return $search
            ->where("user_id", $user_id)
            ->limit($limit, $offset)
            ->exec();
    }

    public function getPostCount($showDeleted = false)
    {
        $search = Post::search();
        if (!$showDeleted) {
            $search->where("deleted", "no");
        }
        return $search->count();
    }

    public function getLatestPost()
    {
        return Post::search()
            ->order("post_id", "DESC")
            ->execOne();
    }

    /**
     * @param int $post_id
     * @param bool $logicalDelete
     * @return Post
     * @throws DeletePostException
     */
    public function deletePost(int $post_id, $logicalDelete = true)
    {
        if ($post_id > 0) {
            $post = Post::search()->where("post_id", $post_id)->execOne();
            if ($post instanceof Post) {
                $sUser = $this->sessionService->getUser();
                if ($sUser instanceof User) {
                    if ($sUser->user_id == $post->user_id) {
                        if ($logicalDelete) {
                            $post->logicalDelete();
                        } else {
                            $post->delete();
                        }
                        return $post;
                    }
                    throw new DeletePostException("User Not Valid : \"{$sUser->user_id}\"");
                }
                throw new DeletePostException("No Valid User Session");
            }
            throw new DeletePostException("No post with id : \"{$post_id}\"");
        }
        throw new DeletePostException("Invalid Post ID : \"{$post_id}\"");
    }

    /**
     * @param int $post_id
     * @param string $title
     * @param string $body
     * @return false|Post
     * @throws EditPostException
     */
    public function editPost(int $post_id, string $title = "", string $body = "")
    {
        if ($post_id > 0) {
            $post = $this->getByID($post_id);
            if ($post instanceof Post) {
                $sUser = $this->sessionService->getUser();
                if ($sUser) {
                    if ($post->user_id != $sUser->user_id) {
                        throw new EditPostException("User ID does not match post");
                    }
                    $change = false;
                    if (strlen($title) > 2) {
                        $post->title = $title;
                        $change = true;
                    }
                    if (strlen($body) > 2) {
                        $post->body = $body;
                        $change = true;
                    }
                    if (!$change) {
                        throw new EditPostException("Title or Body invalid");
                    }
                    $post->save();
                    return $post;
                }
            }
            throw new EditPostException("No post found with id : \"{$post_id}\"");
        }
        throw new EditPostException("Post ID invalid : \"{$post_id}\""); //Edit has failed return false
    }

    /**
     * @param string $title
     * @param string $body
     * @return Post
     * @throws NewPostException
     */
    public function newPost(string $title, string $body)
    {
        if (strlen($title) < 3) {
            throw new NewPostException("Post title too short : \"{$title}\"");
        }
        if (strlen($body) < 3) {
            throw new NewPostException("Post body too short : \"{$title}\"");
        }
        $sUser = $this->sessionService->getUser();
        if ($sUser) {
            $post = new Post();
            $post->user_id = $sUser->user_id;
            $post->title = $title;
            $post->body = $body;
            $post->save();
            return $post;
        }
        throw new NewPostException("No valid user session for new post.");
    }
}
