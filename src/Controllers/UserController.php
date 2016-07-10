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

    protected $error;

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
                [
                    "error" => $this->error
                ]
            );
    }

    public function doLogout(Request $request, Response $response, $args)
    {
        $this->userService->logoutUser();
        return $response->withRedirect("/");
    }

    public function doLogin(Request $request, Response $response, $args)
    {
        $email = $request->getParam("email");
        $password = $request->getParam("password");

        if (strlen($password) < 6 || strlen($email) < 3) {
            $this->error = "pass/email too short";
            return $this->showLogin($request, $response, $args);
        }
        if ($this->userService->loginUser($email, $password)) {
            return $response->withRedirect("/user/dashboard");
        } else {
            $this->error = "login FAIL";
            return $this->showLogin($request, $response, $args);
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

    public function showDashboard(Request $request, Response $response, $args)
    {
        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }

        return $this->twig
            ->render(
                $response,
                'users/dashboard.html.twig',
                [
                    "user" => $sUser
                ]
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
                return $response->withRedirect("/user/login");
            }
        }
        return $this->showRegister($request, $response, $args);
    }
}
