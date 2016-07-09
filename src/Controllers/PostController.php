<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Models\Post;
use Tato\Services\CommentService;
use Tato\Services\PostService;

class PostController
{
    /** @var Twig  */
    protected $twig;
    /** @var PostService  */
    protected $postService;
    /** @var CommentService  */
    protected $commentService;

    public function __construct(Twig $twig, PostService $postService, CommentService $commentService)
    {
        $this->twig = $twig;
        $this->postService = $postService;
        $this->commentService = $commentService;
    }

    public function showPost(Request $request, Response $response, $args)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        $comments = $this->commentService->getByPostID($post->post_id);
        return $this->twig
            ->render(
                $response,
                'posts/view.html.twig',
                [
                    "post" => $post,
                    "comments" => $comments
                ]
            );
    }

    public function showNewPost(Request $request, Response $response, $args)
    {
        $post = new Post();
        return $this->twig
            ->render(
                $response,
                'posts/edit.html.twig',
                [
                    "post" => $post
                ]
            );
    }

    public function showPostList(Request $request, Response $response, $args)
    {
    }

    public function showEditPost(Request $request, Response $response, $args)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        return $this->twig
            ->render(
                $response,
                'posts/edit.html.twig',
                [
                    "post" => $post
                ]
            );
    }

    public function doEditPost(Request $request, Response $response, $args)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        $post->title = $request->getParam("title");
        $post->body = $request->getParam("body");
        $post->save();
        return $response->withRedirect("/posts/{$post->post_id}");
    }

    public function doNewPost(Request $request, Response $response, $args)
    {
        $post = new Post();
        $post->title = $request->getParam("title");
        $post->body = $request->getParam("body");
        $post->created = date("Y-m-d H:i:s");
        $post->save();
        return $response->withRedirect("/posts/{$post->post_id}");
    }
}
