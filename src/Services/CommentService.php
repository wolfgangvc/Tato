<?php
namespace Tato\Services;

use Tato\Models\Comment;

class CommentService
{
    public function __construct()
    {
    }

    public function getByID(int $id)
    {
        return Comment::search()->where("comment_id", $id)->execOne();
    }

    public function getByPostID(int $id)
    {
        return Comment::search()->where("post_id", $id)->order("created", "DESC")->exec();
    }
}
