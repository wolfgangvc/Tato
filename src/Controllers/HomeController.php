<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Services\PostService;

class HomeController
{
    /** @var Twig  */
    protected $twig;

    /** @var PostService  */
    protected $postService;

    public function __construct(Twig $twig, PostService $postService)
    {
        $this->twig = $twig;
        $this->postService = $postService;
    }

    public function showHomePage(Request $request, Response $response, $args)
    {
        $post = $this->postService->getLatestPost();
        return $this->twig
            ->render(
                $response,
                'home/home.html.twig',
                [
                    "post" => $post
                ]
            );
    }
}
