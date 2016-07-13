<?php

namespace Tato\Test\Mocks;

use Tato\Models\User;

class TestSessionService
{
    /** @var User */
    protected $fakeUser;

    protected $session = null;

    public function __construct()
    {
    }

    public function start()
    {
        $this->session = array();
    }

    public function destroy()
    {
        $this->session = null;
    }

    /**
     * @return false|User
     */
    public function getUser()
    {
        if (isset($this->session["user"])) {
            $sUser = $this->session["user"];
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
        if (isset($user) && isset($this->session)) {
            $this->session["user"] = $user;
            return $user;
        }
        return false;
    }
}
