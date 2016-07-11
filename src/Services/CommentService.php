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

    public function newComment(int $post_id, string $title, string $body)
    {
        if ($post_id > 0) { //TODO : replace with check if post exists (give this class access to the postervice)
            if (isset($SESSION["user"])) {
                $sUser = $_SESSION["user"];
                $comment = new Comment();
                $comment->post_id = $post_id;
                $comment->user_id = $sUser["user_id"];
                $comment->title = $title;
                $comment->body = $body;
                $comment->save();
                return $comment;
            }
        }
        return false;
    }
}
