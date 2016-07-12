<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Models\Comment;
use Tato\Models\Post;
use Tato\Models\User;
use Tato\Services\CommentService;

class CommentController
{
    /** @var Twig  */
    protected $twig;
    /** @var CommentService  */
    protected $commentService;

    public function __construct(Twig $twig, CommentService $commentService)
    {
        $this->twig = $twig;
        $this->commentService = $commentService;
    }

    public function showDeleteComment(Request $request, Response $response, $args, $fail = false)
    {
        $comment = $this->commentService->getByID($args["id"]);
        if (!$comment instanceof Comment) {
            return $response->withStatus(404, "COMMENT NOT FOUND");
        }
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        if ($sUser instanceof User) {
            if ($sUser->user_id == $comment->user_id) {
                return $this->twig
                    ->render(
                        $response,
                        'comments/delete.html.twig',
                        [
                            "comment" => $comment,
                            "user" => $sUser,
                            "deleteFail" => $fail
                        ]
                    );
            }
        }
        return $response->withRedirect("/post/" . $comment->post_id);
    }

    public function doDeleteComment(Request $request, Response $response, $args)
    {
        $comment = $this->commentService->getByID($args["id"]);
        if (!$comment instanceof Comment) {
            return $response->withStatus(404, "COMMENT NOT FOUND");
        }
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }

        if ($sUser instanceof User) {
            if ($sUser->user_id == $comment->user_id) {
                if ($request->getParam("title") == $comment->title) {
                    $this->commentService->deleteComment($comment);
                    return $response->withRedirect("/post/" . $comment->post_id);
                } else {
                    return $this->showDeleteComment($request, $response, $args, true);
                }
            }
        }
        return $response->withRedirect("/post/" . $comment->post_id);
    }

    public function doEditComment(Request $request, Response $response, $args)
    {
        $sUser = $_SESSION["user"];
        $post_id = $request->getParam("post_id");
        if (!$sUser instanceof User) {
            return $response->withRedirect("/post/{$post_id}");
        }

        $comment_id = (int)$request->getParam("comment_id");
        $title = $request->getParam("title");
        $body = $request->getParam("body");

        $comment = $this->commentService->getByID($comment_id);
        if ($comment instanceof Comment
            && $comment->user_id == $sUser
            && $comment->post_id != $post_id) {
            $this->commentService->editComment($comment_id, $title, $body);
        } else {
            $this->commentService->newComment($post_id, $title, $body);
        }

        return $response->withRedirect("/post/" . $post_id);
    }

    public function showEditComment(Request $request, Response $response, $args)
    {
        $comment = $this->commentService->getByID($args["id"]);
        if (!$comment instanceof Comment) {
            return $response->withStatus(404, "Comment NOT FOUND");
        }

        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }

        return $this->twig
            ->render(
                $response,
                'comments/editComment.html.twig',
                [
                    "comment" => $comment,
                    "user" => $sUser
                ]
            );
    }
}
