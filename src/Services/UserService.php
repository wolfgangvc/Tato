<?php
namespace Tato\Services;

use Tato\Exceptions\UserLoginException;
use Tato\Exceptions\UserRegistrationException;
use Tato\Models\Group;
use Tato\Models\User;

class UserService
{
    /** @var SessionService  */
    protected $sessionService;
    /** @var GroupService  */
    protected $groupService;


    public function __construct($sessionService, GroupService $groupService)
    {
        $this->sessionService = $sessionService;
        $this->groupService = $groupService;
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

    public function getByName(string $name, $includeDeleted = false)
    {
        $name = strtolower($name);
        $search = User::search();
        if (!$includeDeleted) {
            $search->where("deleted", User::STATE_IS_NOT_DELETED);
        }
        return $search
            ->where("name", $name)
            ->execOne();
    }

    public function deleteUser(int $user_id, $logicalDelete = true)
    {
        $user = User::search()->where("user_id", $user_id)->execOne();
        if (!$user instanceof User) {
            throw new UserRegistrationException("Invalid User ID to delete : \"{$user_id}\"");
        }

        if ($logicalDelete) {
            $user->logicalDelete();
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

        if (strlen($displayName) < 3) {
            $displayName = $username;
        }

        $username = strtolower($username);

        // Check for valid email address
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

        
        
        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->display_name = $displayName;
        $user->verify_key = sha1((string)rand(100000000, 999999999));
        $user->pass = password_hash($password, PASSWORD_DEFAULT);
        $user->save();

        $this->addToDefaultGroups($user);

        return $user;
    }

    protected function addToDefaultGroups(User $user)
    {
        $groups = $this->groupService->getDefaultGroups();
        if (count($groups) > 0) {
            /** @var $group Group*/
            foreach ($groups as $group) {
                $this->groupService->addUserToGroup($user, $group);
            }
        }
    }

    public function logoutUser()
    {
        $this->sessionService->destroy();
    }

    /**
     * @param string $userString
     * @param string $password
     * @return false|User
     * @throws UserLoginException
     * @throws \Thru\ActiveRecord\Exception
     */
    public function loginUser(string $userString, string $password)
    {
        $user = User::search()->where("name", $userString)->execOne();
        if (!$user instanceof User) {
            $user = User::search()->where("email", $userString)->execOne();
            if (!$user instanceof User) {
                throw new UserLoginException("No user found with username/email : \"{$userString}\"");
            }
        }
        if (password_verify($password, $user->pass)) {
            return $this->sessionService->setUser($user);
        } else {
            return false;
        }
    }
}
