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
        return Post::search()->where("post_id", $id)->execOne();
    }

    public function getPosts(int $count, int $first)
    {
        $count = ($count < 1) ? 1 : $count;
        $first = ($first < 0) ? 0 : $first;
        return Post::search()->limit($count, $first)->exec();
    }

    public function getPostCount()
    {
        return Post::search()->count();
    }
}
