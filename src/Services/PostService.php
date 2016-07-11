<?php
namespace Tato\Services;

use Tato\Models\Post;

class PostService
{
    public function __construct()
    {
    }

    public function getByID(int $id)
    {
        return Post::search()
            ->where("deleted", "no")
            ->where("post_id", $id)
            ->execOne();
    }

    public function getPosts(int $limit = 10, int $offset = 0)
    {
        $limit = ($limit < 1) ? 1 : $limit;
        $offset = ($offset < 0) ? 0 : $offset;
        return Post::search()
            ->where("deleted", "no")
            ->limit($limit, $offset)
            ->exec();
    }

    public function getPostsByUser(int $user_id, int $limit = 10, int $offset = 0)
    {
        if ($user_id < 1) {
            return false;
        }

        return Post::search()
            ->where("deleted", "no")
            ->where("user_id", $user_id)
            ->limit($limit, $offset)
            ->exec();
    }

    public function getPostCount()
    {
        return Post::search()->count();
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
                $post->deleted = "yes";
                $post->save();
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
        if (isset($SESSION["user"])) {
            $sUser = $_SESSION["user"];
            $post = new Post();
            $post->user_id = $sUser["user_id"];
            $post->title = $title;
            $post->body = $body;
            $post->save();
            return $post;
        }
        return false; //new post has failed return false
    }
}
