<?php
namespace Tato\Services;

use Tato\Exceptions\NewCommentException;
use Tato\Models\Comment;
use Tato\Models\Post;
use Tato\Models\User;

class CommentService
{
    /** @var SessionService */
    protected $sessionService;
    /** @var PostService */
    protected $postService;

    public function __construct($sessionService, PostService $postService)
    {
        $this->sessionService = $sessionService;
        $this->postService = $postService;
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

    public function deleteComment(Comment $comment, $logicalDelete = true)
    {
        $comment->logicalDelete();
        return true;
    }

    public function deleteCommentByID(int $comment_id, $logicalDelete = true)
    {
        $comment = $this->getByID($comment_id);
        if ($comment instanceof Comment) {
            return $this->deleteComment($comment, $logicalDelete);
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
        if (strlen($title) > 2 && strlen($body) > 2) {
            if ($post_id > 0) {
                $sUser = $this->sessionService->getUser();
                if ($sUser instanceof User) {
                    $post = $this->postService->getByID($post_id);
                    if ($post instanceof Post) {
                        $comment = new Comment();
                        $comment->post_id = $post_id;
                        $comment->user_id = $sUser->user_id;
                        $comment->title = $title;
                        $comment->body = $body;
                        $comment->save();
                        return $comment;
                    }
                    throw new NewCommentException("No Post With ID : \"{$post_id}\"");
                }
                throw new NewCommentException("Invalid User Session");
            }
            throw new NewCommentException("Invalid Post ID : \"{$post_id}\"");
        }
        throw new NewCommentException("Title and/or body too short");
    }
}
