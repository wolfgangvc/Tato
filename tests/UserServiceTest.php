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

    public function testUserServiceNewUser()
    {
        $this->assertEquals(true, $this->userService->newUser("Bob@Mail.com", "bob", "bobpass", "Display Bob"));
        $user = $this->userService->getByID(1);
        if ($user instanceof User) {
            $this->assertEquals("bob", $user->name);
        }
    }
}
