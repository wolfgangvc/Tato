<?php
namespace Tato\Controllers;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class HomeController
{
    /** @var \Interop\Container\ContainerInterface */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function showHomePage(Request $request, Response $response, $args)
    {
        return $this->container
            ->get('view')
            ->render(
                $response,
                'dashboard/home.html.twig',
                [
                    "rand" => rand(1, 10)
                ]
            );
    }
}