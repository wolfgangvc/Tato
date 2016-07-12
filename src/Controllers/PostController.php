<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Models\Post;
use Tato\Models\User;
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

        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        
        return $this->twig
            ->render(
                $response,
                'posts/view.html.twig',
                [
                    "post" => $post,
                    "comments" => $comments,
                    "user" => $sUser
                ]
            );
    }

    public function showNewPost(Request $request, Response $response, $args)
    {
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        } else {
            return $response->withRedirect("/");
        }
        $post = new Post();

        return $this->twig
            ->render(
                $response,
                'posts/edit.html.twig',
                [
                    "post" => $post,
                    "user" => $sUser
                ]
            );
    }

    public function showPosts(Request $request, Response $response, $args)
    {
        $page = (int) $args["page"];
        if ($page < 1) {
            $page = 1;
        }
        $page--;

        $posts = $this->postService->getPosts(10, $page * 10);

        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }

        return $this->twig
            ->render(
                $response,
                'posts/posts.html.twig',
                [
                    "posts" => $posts,
                    "page"  => $page + 1,
                    "pages" => ceil($this->postService->getPostCount() / 10),
                    "user" => $sUser
                ]
            );
    }

    public function showEditPost(Request $request, Response $response, $args)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        return $this->twig
            ->render(
                $response,
                'posts/edit.html.twig',
                [
                    "post" => $post,
                    "user" => $sUser
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
        return $response->withRedirect("/post/{$post->post_id}");
    }

    public function doNewPost(Request $request, Response $response, $args)
    {
        $post = $this->postService->newPost(
            $request->getParam("title"),
            $request->getParam("body")
        );
        if ($post instanceof Post) {
            return $response->withRedirect("/post/{$post->post_id}");
        } else {
            return $this->showNewPost($request, $response, $args);
        }
    }

    public function showDeletePost(Request $request, Response $response, $args, $fail = false)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        if ($sUser instanceof User) {
            if ($sUser->user_id == $post->user_id) {
                return $this->twig
                    ->render(
                        $response,
                        'posts/delete.html.twig',
                        [
                            "post" => $post,
                            "user" => $sUser,
                            "deleteFail" => $fail
                        ]
                    );
            }
        }
        return $response->withRedirect("/post/" . $post->post_id);
    }

    public function doDeletePost(Request $request, Response $response, $args)
    {
        $post = $this->postService->getByID($args["id"]);
        if (!$post instanceof Post) {
            return $response->withStatus(404, "POST NOT FOUND");
        }
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        if ($sUser instanceof User) {
            if ($sUser->user_id == $post->user_id) {
                if ($request->getParam("title") == $post->title) {
                    $this->postService->deletePost($post->post_id);
                    return $response->withRedirect("/posts/1");
                } else {
                    return $this->showDeletePost($request, $response, $args, true);
                }
            }
        }
        return $response->withRedirect("/post/" . $post->post_id);
    }
}
