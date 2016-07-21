<?php
namespace Tato\Controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Tato\Exceptions\UserLoginException;
use Tato\Exceptions\UserRegistrationException;
use Tato\Models\User;
use Tato\Services\CommentService;
use Tato\Services\PostService;
use Tato\Services\SessionService;
use Tato\Services\UserService;

class UserController
{
    /** @var Twig */
    protected $twig;
    /** @var SessionService */
    protected $sessionService;
    /** @var UserService */
    protected $userService;
    /** @var PostService */
    protected $postService;
    /** @var CommentService */
    protected $commentService;

    protected $error;

    public function __construct(
        Twig $twig,
        SessionService $sessionService,
        UserService $userService,
        PostService $postService,
        CommentService $commentService
    ) {
    

        $this->twig = $twig;
        $this->sessionService = $sessionService;
        $this->userService = $userService;
        $this->postService = $postService;
        $this->commentService = $commentService;
    }




    public function showLogin(Request $request, Response $response, $args)
    {
        $sUser = $this->sessionService->getUser();
        if ($sUser) {
            return $response->withRedirect("/user/dashboard");
        }
        $args["hideLogin"] = true;
        $params = $request->getQueryParams();
        if (isset($params["redirect"])) {
            $args["redirect"] = $params["redirect"];
        }
        return $this->twig
            ->render(
                $response,
                'users/register.html.twig',
                $args
            );
    }

    public function showRegister(Request $request, Response $response, $args)
    {
        $sUser = $this->sessionService->getUser();
        if ($sUser) {
            return $response->withRedirect("/user/dashboard");
        }
        $args["register"] = true;
        return $this->twig
            ->render(
                $response,
                'users/register.html.twig',
                $args
            );
    }

    public function doLogin(Request $request, Response $response, $args)
    {
        $params = $request->getParams();
        $redirect = $request->getParam("redirect");
        $password = $request->getParam("password");
        $username = $request->getParam("username");
        if (isset($params["register"])) {
            return $this->showRegister(
                $request,
                $response,
                [
                    "redirect" => $redirect,
                    "username" => $username
                ]
            );
        }

        $error = null;
        try {
            if (!isset($username)) {
                throw new UserLoginException("No Username Provided");
            }
            if (!isset($password)) {
                throw new UserLoginException("No Password Provided");
            }
            if (strlen($password) < 6 || strlen($username) < 3) {
                throw new UserLoginException("Username or Password too short");
            }

            if ($this->userService->loginUser($username, $password)) {
                return $this->showRedirect(
                    $request,
                    $response,
                    [
                        "redirect"=>$redirect,
                        "message"=>"Successfully logged in."
                    ]
                );
            }
        } catch (UserLoginException $ule) {
            $error = $ule->getMessage();
        }
        return $this->showLogin(
            $request,
            $response,
            [
                "redirect" => $params["redirect"],
                "error" => $error
            ]
        );
    }

    public function doRegister(Request $request, Response $response, $args)
    {
        $error = null;
        $redirect = $request->getParam("redirect");
        $email = $request->getParam("email");
        $password = $request->getParam("password");
        $username = $request->getParam("username");
        $password2 = $request->getParam("password2");
        try {
            if ($password != $password2) {
                throw new UserRegistrationException("Passwords didn't match.");
            }
            if ($this->userService->newUser($email, $username, $password, $username)) {
                return $this->showRedirect(
                    $request,
                    $response,
                    [
                        "message" => "Successfully registered.",
                        "redirect" => "/user/login?redirect=" . $redirect
                    ]
                );
            }
        } catch (UserRegistrationException $ure) {
            $error = $ure->getMessage();
        }
        return $this->showRegister(
            $request,
            $response,
            [
                "username" => $username,
                "email" => $email,
                "error" => $error,
                "redirect" => $redirect
            ]
        );
    }

    public function doLogout(Request $request, Response $response, $args)
    {
        $this->userService->logoutUser();
        return $this->showRedirect(
            $request,
            $response,
            [
                "message" => "Logged out.",
                "redirect" => $request->getParam("redirect")
            ]
        );
    }

    public function showRedirect(Request $request, Response $response, $args)
    {
        if (isset($args["redirect"])) {
            $message = "";
            if (isset($args["message"])) {
                $message = $args["message"];
            }
            return $this->twig
                ->render(
                    $response->withAddedHeader("refresh", "3;url={$args["redirect"]}"),
                    'utility/redirect.html.twig',
                    [
                        "hideLogin" => true,
                        "redirectMessage" => $message,
                        "redirect" => $args["redirect"]
                    ]
                );
        } else {
            $response->withRedirect("/");
        }
    }

    public function showDashboard(Request $request, Response $response, $args)
    {
        $sUser = $this->sessionService->getUser();
        if ($sUser) {
            return $this->twig
                ->render(
                    $response,
                    'users/dashboard.html.twig',
                    [
                        "user" => $sUser
                    ]
                );
        } else {
            return $response->withRedirect("/user/register");
        }
    }

    public function showUserPage(Request $request, Response $response, $args)
    {
        $pageUser = $this->userService->getByID((int)$args["id"]);
        if (!$pageUser instanceof User) {
            $pageUser = $this->userService->getByName($args["id"]);
            if (!$pageUser instanceof User) {
                return $response->withStatus(404, "USER NOT FOUND");
            }
        }

        $posts = $this->postService->getByUserID($pageUser->user_id);
        $comments = $this->commentService->getByUserID($pageUser->user_id);

        $sUser = null;
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
        }
        return $this->twig
            ->render(
                $response,
                'users/view.html.twig',
                [
                    "user" => $sUser,
                    "pageUser" => $pageUser,
                    "posts" => $posts,
                    "comments" => $comments
                ]
            );
    }
}
