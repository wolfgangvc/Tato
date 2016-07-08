<?php
namespace Tato;

use Slim\App as SlimApp;
use Slim\Http\Request;
use Slim\Http\Response;
use Tato\Controllers\HomeController;
use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

class Tato
{
    /** @var  \Slim\App */
    protected $slim;
    /** @var \Interop\Container\ContainerInterface */
    protected $container;
    public static function Factory()
    {
        return new self();
    }

    public function __construct()
    {
        $this->setupSlim();
        $this->setupTwig();
        $this->setupDependencies();
        $this->setRoots();
    }

    /**
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function setupSlim()
    {
        $this->slim = new SlimApp([
            "settings" => [
                "debug" => true,
                "displayErrorDetails" => true,
                "determinRouteBeforeAppMiddleware" => true
            ]
        ]);
        $this->slim->add(new WhoopsMiddleware());

        $this->container = $this->slim->getContainer();
    }

    protected function setupDependencies()
    {
        $this->container[HomeController::class] = function (\Slim\Container $container) {
            return new HomeController($container);
        };
    }

    protected function setupTwig()
    {
        // Register Twig View helper
        $this->container['view'] = function ($c) {
            $view = new \Slim\Views\Twig(
                '../views/',
                [
                    'cache' => false,
                    'debug' => true
                ]
            );

            // Instantiate and add Slim specific extension
            $view->addExtension(
                new \Slim\Views\TwigExtension(
                    $c['router'],
                    $c['request']->getUri()
                )
            );

            // Added Twig_Extension_Debug to enable twig dump() etc.
            $view->addExtension(
                new \Twig_Extension_Debug()
            );

            $view->addExtension(new \Twig_Extensions_Extension_Text());

            return $view;
        };
    }

    protected function setRoots()
    {
        $this->slim->get("/hello/{name}", function (Request $request, Response $response, $args) {
            $response->write("Hello " . $args['name']);
            return $response;
        });
        $this->slim->get("/", \Tato\Controllers\HomeController::class . ':showHomePage');
    }

    public function run()
    {
        return $this->slim->run();
    }
}
