<?php
namespace Tato\Services;

use Tato\Models\Post;
use Tato\Models\User;

class PostService
{
    public function __construct()
    {
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

    public function deletePost(int $post_id)
    {
        if ($post_id > 0) {
            $post = $this->getByID($post_id);
            if ($post instanceof Post) {
                $post->logicalDelete();
                return $post;
            }
        }
        return false;
    }
    
    public function editPost(int $post_id, $args)
    {
        if ($post_id > 0 && is_array($args)) {
            $post = $this->getByID($post_id);
            if ($post instanceof Post) {
                if (isset($args["title"])) {
                    $post->title = $args["title"];
                }
                if (isset($args["body"])) {
                    $post->title = $args["body"];
                }
            }
        }
        return false; //Edit has failed return false
    }
    
    
    public function newPost(string $title, string $body)
    {
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
            if ($sUser instanceof User) {
                $post = new Post();
                $post->user_id = $sUser->user_id;
                $post->title = $title;
                $post->body = $body;
                $post->save();
                return $post;
            }
        }
        return false; //new post has failed return false
    }
}
