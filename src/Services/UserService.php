<?php
namespace Tato\Services;

use Tato\Models\User;

class UserService
{
    public function __construct()
    {
    }

    public function getByID(int $id)
    {
        return User::search()->where("user_id", $id)->execOne();
    }

    public function deleteUser(int $user_id)
    {
        $user = User::search()->where("user_id", $user_id)->execOne();
        if (!$user instanceof User) {
            return false;
        }
        $user->deleted = "yes";
        $user->save();
        return true;
    }
    
    public function newUser(string $email, string $username, string $password, string $displayName = "")
    {
        $email = strtolower($email);
        $username = strtolower($username);
        // Check for valid email address
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            return false;
        }

        // Check that the user's email is not taken
        $user = User::search()->where("email", $email)->execOne();
        if ($user instanceof User) {
            return false;
        }

        // Check that username is valid
        if (preg_replace("/[^A-Za-z0-9]/", '', $username) != $username || strlen($username) < 3) {
            return false;
        }

        // Check that username is not yet taken
        $user = User::search()->where("name", $username)->execOne();
        if ($user instanceof User) {
            return false;
        }

        // Check that password is longer than 6 chars
        if (strlen($password) < 6) {
            return false;
        }

        if (strlen($displayName) < 3) {
            $displayName = $username;
        }

        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->display_name = $displayName;
        $user->pass = password_hash($password, PASSWORD_DEFAULT);
        $user->save();
        if ($user->user_id > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function logoutUser()
    {
        session_destroy();
    }
    
    public function loginUser(string $userString, string $password)
    {
        $user = User::search()->where("name", $userString)->execOne();
        if (!$user instanceof User) {
            $user = User::search()->where("email", $userString)->execOne();
            if (!$user instanceof User) {
                return false;
            }
        }
        if (password_verify($password, $user->pass)) {
            $_SESSION["user"] = $user;
            return true;
        } else {
            return false;
        }
    }

    public function checkPassword(string $userString, string $password)
    {
        $user = User::search()->where("name", $userString)->execOne();
        if (!$user instanceof User) {
            $user = User::search()->where("email", $userString)->execOne();
            if (!$user instanceof User) {
                return false;
            }
        }
        return password_verify($password, $user->pass);
    }
}
