<?php

namespace Tato\Test;

use Tato\Services\UserService;
use Tato\Models\User;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  UserService */
    protected $userService;

    public function setUp()
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function testUserServiceNewUserValid()
    {
        $this->assertEquals(true, $this->userService->newUser("Bob@Mail.com", "bob", "bobpass", "Display Bob"));
        $user = $this->userService->getByID(1);
        if ($user instanceof User) {
            $this->assertEquals("bob", $user->name);
            $this->assertEquals("bob@mail.com", $user->email);
            $this->assertEquals("Display Bob", $user->display_name);
            $this->assertEquals(true, password_verify("bobpass", $user->pass));
        }
    }
    public function testUserServiceNewUserInvalid()
    {
        // test when no strings are passed
        $this->assertEquals(false, $this->userService->newUser("", "", ""));

        //test invalid email
        $this->assertEquals(false, $this->userService->newUser("barbara", "barbara", "barpass"));

        //test invalid username
        $this->assertEquals(false, $this->userService->newUser("barbara@mail.com", "b", "barpass"));

        //test invalid password
        $this->assertEquals(false, $this->userService->newUser("barbara@mail.com", "barbara", "bar"));
    }
}
