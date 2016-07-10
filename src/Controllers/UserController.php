<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Services\UserService;

class UserController
{
    /** @var Twig  */
    protected $twig;
    /** @var UserService  */
    protected $userService;

    public function __construct(Twig $twig, UserService $userService)
    {
        $this->twig = $twig;
        $this->userService = $userService;
    }

    public function showLogin(Request $request, Response $response, $args)
    {
        return $this->twig
            ->render(
                $response,
                'users/login.html.twig',
                []
            );
    }

    public function doLogin(Request $request, Response $response, $args)
    {
        $email = $request->getParam("email");
        $password = $request->getParam("password");

        if (strlen($password < 6) || strlen($email) < 3) {
            return $this->showLogin($request, $response, $args);
        }
        if ($this->userService->loginUser($email, $password)) {
            $response->withRedirect("/");
        }
    }

    public function showRegister(Request $request, Response $response, $args)
    {
        return $this->twig
            ->render(
                $response,
                'users/register.html.twig',
                []
            );
    }

    public function doRegister(Request $request, Response $response, $args)
    {
        $email = $request->getParam("email");
        $password = $request->getParam("password");
        $username = $request->getParam("username");
        $password2 = $request->getParam("password2");
        if ($password == $password2) {
            if ($this->userService->newUser($email, $username, $password, $username)) {
                return $this->showLogin($request, $response, $args);
            }
        }
        return $this->showRegister($request, $response, $args);
    }
}
