<?php
namespace Tato\Services;

use Tato\Models\User;

class SessionService
{
    public function __construct()
    {
    }

    public function start()
    {
        session_start();
    }

    public function destroy()
    {
        session_destroy();
    }

    /**
     * @return false|User
     */
    public function getUser()
    {
        if (isset($_SESSION["user"])) {
            $sUser = $_SESSION["user"];
            if ($sUser instanceof User) {
                return $sUser;
            }
        }
        return false;
    }

    /**
     * @param User $user
     * @return false|User
     */
    public function setUser(User $user)
    {
        if (isset($user) && isset($_SESSION)) {
            $_SESSION["user"] = $user;
            return $user;
        }
        return false;
    }
}
