<?php
namespace Tato\Services;

use Tato\Models\Comment;
use Tato\Models\User;

class CommentService
{
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function getByID(int $id)
    {
        return Comment::search()
            ->where("comment_id", $id)
            ->where('deleted', Comment::STATE_IS_NOT_DELETED)
            ->execOne();
    }

    public function getByPostID(int $id)
    {
        return Comment::search()
            ->where("post_id", $id)
            ->where("deleted", Comment::STATE_IS_NOT_DELETED)
            ->order("created", "DESC")
            ->exec();
    }

    public function getByUserID(int $id)
    {
        return Comment::search()
            ->where("user_id", $id)
            ->where("deleted", Comment::STATE_IS_NOT_DELETED)
            ->order("created", "DESC")
            ->exec();
    }

    public function deleteComment(Comment $comment)
    {
        $comment->logicalDelete();
        return true;
    }

    public function deleteCommentByID(int $comment_id)
    {
        $comment = $this->getByID($comment_id);
        if ($comment instanceof Comment) {
            return $this->deleteComment($comment);
        }
        return false;
    }

    public function editComment(int $comment_id, string $title, string $body)
    {
        $comment = Comment::search()->where("comment_id", $comment_id)->execOne();
        if ($comment instanceof Comment) {
            $sUser = $this->sessionService->getUser();
            if ($sUser) {
                if ($comment->user_id == $sUser->user_id) {
                    $comment->title = $title;
                    $comment->body = $body;
                    $comment->save();
                    return $comment;
                }
            }
        }
        return false;
    }

    public function newComment(int $post_id, string $title, string $body)
    {
        if ($post_id > 0) { //TODO : replace with check if post exists (give this class access to the postervice)
                $sUser = $this->sessionService->getUser();
            if ($sUser) {
                $comment = new Comment();
                $comment->post_id = $post_id;
                $comment->user_id = $sUser->user_id;
                $comment->title = $title;
                $comment->body = $body;
                $comment->save();
                return $comment;
            }
        }
        return false;
    }
}
