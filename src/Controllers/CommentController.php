<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Models\Comment;
use Tato\Models\Post;
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

    public function doDeleteComment(Request $request, Response $response, $args)
    {
        $comment_id = $request->getParam("comment_id");
        $comment = $this->commentService->getByID($comment_id);
        if (!$comment instanceof Comment) {
            return $response->withStatus(404, "Comment NOT FOUND");
        }
        $comment->deleted = "YES";
        $comment->save();
        return $response->withRedirect("/posts/{$comment->post_id}");
    }

    public function doNewComment(Request $request, Response $response, $args)
    {
        $comment = new Comment();
        $comment->post_id = $request->getParam("post_id");
        $comment->title = $request->getParam("title");
        $comment->body = $request->getParam("body");
        $comment->created = date("Y-m-d H:i:s");
        $comment->save();
        return $response->withRedirect("/posts/{$comment->post_id}");
    }

    public function doEditComment(Request $request, Response $response, $args)
    {
        $comment_id = $request->getParam("comment_id");
        $comment = $this->commentService->getByID($comment_id);
        if (!$comment instanceof Comment) {
            return $response->withStatus(404, "Comment NOT FOUND");
        }
        $comment->title = $request->getParam("title");
        $comment->body = $request->getParam("body");
        $comment->save();
        return $response->withRedirect("/posts/{$comment->post_id}");
    }
}
