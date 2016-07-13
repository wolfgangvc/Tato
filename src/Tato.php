<?php
namespace Tato;

use Slim\App as SlimApp;
use Slim\Http\Request;
use Slim\Http\Response;
use Tato\Controllers\CommentController;
use Tato\Controllers\HomeController;
use Tato\Controllers\PostController;
use Tato\Controllers\UserController;
use Tato\Extensions\TwigMarkdownExtension;
use Tato\Services\CommentService;
use Tato\Services\PostService;
use Tato\Services\SessionService;
use Tato\Services\UserService;
use Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware;

class Tato
{
    /** @var  \Slim\App */
    protected $slim;
    /** @var \Interop\Container\ContainerInterface */
    protected $container;
    public static function factory()
    {
        return new self();
    }

    public function __construct()
    {
        $this->setupSlim();
        $this->setupTwig();
        $this->setupDependencies();
        $this->setRoots();
        $this->setupSession();
    }

    /**
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function setupSession()
    {
        $sessionService = $this->container->get(SessionService::class);
        if ($sessionService instanceof SessionService) {
            $sessionService->start();
        }
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
            return new HomeController(
                $container->get("view"),
                $container->get(PostService::class)
            );
        };
        $this->container[PostController::class] = function (\Slim\Container $container) {
            return new PostController(
                $container->get("view"),
                $container->get(PostService::class),
                $container->get(CommentService::class)
            );
        };
        $this->container[CommentController::class] = function (\Slim\Container $container) {
            return new CommentController(
                $container->get("view"),
                $container->get(CommentService::class)
            );
        };
        $this->container[UserController::class] = function (\Slim\Container $container) {
            return new UserController(
                $container->get("view"),
                $container->get(UserService::class),
                $container->get(PostService::class),
                $container->get(CommentService::class)
            );
        };
        $this->container[SessionService::class] = function (\Slim\Container $container) {
            return new SessionService();
        };
        $this->container[PostService::class] = function (\Slim\Container $container) {
            return new PostService(
                $container->get(SessionService::class)
            );
        };
        $this->container[CommentService::class] = function (\Slim\Container $container) {
            return new CommentService(
                $container->get(SessionService::class)
            );
        };
        $this->container[UserService::class] = function (\Slim\Container $container) {
            return new UserService(
                $container->get(SessionService::class)
            );
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
            
            $view->addExtension(new TwigMarkdownExtension());

            return $view;
        };
    }

    protected function setRoots()
    {
        $this->slim->get("/hello/{name}", function (Request $request, Response $response, $args) {
            $response->write("Hello " . $args['name']);
            return $response;
        });
        $this->slim->get("/", HomeController::class . ':showHomePage');
        $this->slim->get("/posts/{page}", PostController::class . ":showPosts");
        $this->slim->group("/post", function () {
            $this->get("/new", PostController::class . ':showNewPost');
            $this->post("/new", PostController::class . ':doNewPost');
            $this->group("/edit", function () {
                $this->get("/{id}", PostController::class . ':showEditPost');
                $this->post("/{id}", PostController::class . ':doEditPost');
            });
            $this->group("/delete", function () {
                $this->get("/{id}", PostController::class . ':showDeletePost');
                $this->post("/{id}", PostController::class . ':doDeletePost');
            });
            $this->get("/{id}", PostController::class . ':showPost');
        });
        $this->slim->group("/comment", function () {
            $this->group("/edit", function () {
                $this->get("/{id}", CommentController::class . ":showEditComment");
                $this->post("/{id}", CommentController::class . ":doEditComment");
                $this->post("", CommentController::class . ":doEditComment");
            });
            $this->group("/delete", function () {
                $this->get("/{id}", CommentController::class . ":showDeleteComment");
                $this->post("/{id}", CommentController::class . ":doDeleteComment");
            });
        });
        $this->slim->group("/user", function () {
            $this->get("/login", UserController::class . ":showLogin");
            $this->post("/login", UserController::class . ":doLogin");
            $this->get("/logout", UserController::class . ":doLogout");
            $this->get("/register", UserController::class . ":showRegister");
            $this->post("/register", UserController::class . ":doRegister");
            $this->get("/dashboard", UserController::class . ":showDashboard");
            $this->get("/{id}", UserController::class . ":showUserPage");
        });
        /*
        $this->slim->get("/posts/new",PostController::class . ':showNewPost');
        $this->slim->post("/posts/new",PostController::class . ':doNewPost');
        $this->slim->get("/posts/{id}",PostController::class . ':showPost');
        */
    }

    public function run()
    {
        return $this->slim->run();
    }
}
