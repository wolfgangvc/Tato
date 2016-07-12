<?php
namespace Tato\Services;

use Tato\Exceptions\UserRegistrationException;
use Tato\Models\User;

class UserService
{
    public function __construct()
    {
    }

    public function getByID(int $id, $includeDeleted = false)
    {
        $search = User::search();
        if (!$includeDeleted) {
            $search->where("deleted", User::STATE_IS_NOT_DELETED);
        }
        return $search
            ->where("user_id", $id)
            ->execOne();
    }

    public function deleteUser(int $user_id, $logicalDelete = true)
    {
        $user = User::search()->where("user_id", $user_id)->execOne();
        if (!$user instanceof User) {
            throw new UserRegistrationException("Invalid User ID to delete : \"{$user_id}\"");
        }

        if ($logicalDelete) {
            $user->deleted = User::STATE_IS_DELETED;
            $user->save();
        } else {
            $user->delete();
        }

        return $user;
    }

    /**
     * @param string $email
     * @param string $username
     * @param string $password
     * @param string $displayName
     * @return false|User
     * @throws \Thru\ActiveRecord\Exception
     * @throws UserRegistrationException
     */
    public function newUser(string $email, string $username, string $password, string $displayName = "")
    {
        $email = strtolower($email);
        $username = strtolower($username);
        // Check for valid email address
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new UserRegistrationException("Email Invalid : \"{$email}\"");
        }

        // Check that the user's email is not taken
        $user = User::search()->where("email", $email)->execOne();
        if ($user instanceof User) {
            throw new UserRegistrationException("Email Taken : \"{$email}\"");
        }

        // Check that username is valid
        if (preg_replace("/[^A-Za-z0-9._-]/", '', $username) != $username || strlen($username) < 3) {
            throw new UserRegistrationException("Username Invalid : \"{$username}\"");
        }

        // Check that username is not yet taken
        $user = User::search()->where("name", $username)->execOne();
        if ($user instanceof User) {
            throw new UserRegistrationException("Username Taken : \"{$username}\"");
        }

        // Check that password is longer than 6 chars
        if (strlen($password) < 6) {
            throw new UserRegistrationException("Password Too Short : \"{$password}\"");
        }

        if (strlen($displayName) < 3) {
            $displayName = $username;
        }

        
        
        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->display_name = $displayName;
        $user->verify_key = sha1((string)rand(100000000, 999999999));
        $user->pass = password_hash($password, PASSWORD_DEFAULT);
        $user->save();

        return $user;
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
}
