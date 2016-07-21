<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Services\PageService;

class PageController
{
    /** @var Twig */
    protected $twig;
    /** @var PageService */
    protected $pageService;

    public function __construct(Twig $twig, PageService $pageService)
    {
        $this->twig = $twig;
        $this->pageService = $pageService;
    }

    public function showPage(Request $request, Response $response, $args)
    {
        $page = $args["page"];
        //$this->pageService->getByName($name);
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        return $this->twig
            ->render(
                $response,
                'home/home.html.twig',
                [
                    "page" => $page,
                    "user" => $sUser
                ]
            );
    }
}
