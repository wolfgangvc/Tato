<?php

namespace Tato\Test;

use Tato\Exceptions\UserLoginException;
use Tato\Services\TestSessionService;
use Tato\Services\UserService;
use Tato\Models\User;
use Thru\ActiveRecord\SearchIndex;

class UserServiceTest extends BaseTest
{
    /** @var  UserService */
    protected $userService;
    /** @var  String */
    protected $password = "Fak3P455**";

    /** @var  TestSessionService */
    protected $sessionService;

    public function setUp()
    {
        parent::setUp();

        $this->sessionService = new TestSessionService();
        $this->sessionService->start();
        $this->userService = new UserService($this->sessionService);
    }

    /**
     * @expectedException \Tato\Exceptions\UserLoginException
     * @expectedExceptionMessage No user found with username/email :
     */
    public function testUserServiceLoginNotRegisteredUsername()
    {
        $this->userService->loginUser(
            $this->faker->userName,
            $this->password
        );
    }


    /**
     * @expectedException \Tato\Exceptions\UserLoginException
     * @expectedExceptionMessage No user found with username/email :
     */
    public function testUserServiceLoginNotRegisteredEmail()
    {
        $this->userService->loginUser(
            $this->faker->safeEmail,
            $this->password
        );
    }

    /**
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessage Email Invalid :
     */
    public function testUserServiceInvalidEmail()
    {
        $this->userService->newUser("fake", "", "", "");
    }

    /**
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessageRegExp /Username Invalid : \"(.*)\"/
     */
    public function testUserServiceInvalidUsername()
    {
        $this->userService->newUser(
            $this->faker->safeEmail,
            "fake name",
            $this->faker->password,
            $this->faker->name
        );
    }

    /**
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessageRegExp /Password Too Short : \"(.*)\"/
     */
    public function testUserServicePasswordTooShort()
    {
        $this->userService->newUser(
            $this->faker->safeEmail,
            $this->faker->userName,
            "pass",
            $this->faker->name
        );
    }

    public function testUserServiceNewUser()
    {
        /** @var $testUser User */
        $email = $this->faker->safeEmail;
        $name = $this->faker->userName;
        $dName = $this->faker->name;
        $testUser = $this->userService->newUser(
            $email,
            $name,
            $this->password,
            $dName
        );

        $this->assertInstanceOf(User::class, $testUser);

        $testUser = User::search()->where("user_id", $testUser->user_id)->execOne();

        $this->assertInstanceOf(User::class, $testUser);

        $this->assertEquals(strtolower($email), $testUser->email);
        $this->assertEquals(strtolower($name), $testUser->name);
        $this->assertTrue(password_verify($this->password, $testUser->pass));
        $this->assertEquals($dName, $testUser->display_name);

        return $testUser;
    }

    /**
     * @depends testUserServiceNewUser
     */
    public function testUserServiceGetByID(User $testUser)
    {
        /** @var $user User */
        $user = $this->userService->getByID($testUser->user_id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($testUser->email, $user->email);
        $this->assertEquals($testUser->name, $user->name);
        $this->assertEquals($testUser->pass, $user->pass);
        $this->assertEquals($testUser->display_name, $user->display_name);
        $this->assertEquals(User::STATE_IS_NOT_DELETED, $user->deleted);
    }

    /**
     * @depends testUserServiceNewUser
     */
    public function testUserServiceGetByName(User $testUser)
    {
        /** @var $user User */
        $user = $this->userService->getByName($testUser->name);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($testUser->email, $user->email);
        $this->assertEquals($testUser->name, $user->name);
        $this->assertEquals($testUser->pass, $user->pass);
        $this->assertEquals($testUser->display_name, $user->display_name);
        $this->assertEquals(User::STATE_IS_NOT_DELETED, $user->deleted);
    }

    /**
     * @depends testUserServiceNewUser
     */
    public function testUserServiceLoginInvalidPassword(User $testUser)
    {
        $this->assertFalse($this->userService->loginUser($testUser->name, "passwd"));
        return $testUser;
    }

    /**
     * @depends testUserServiceNewUser
     */
    public function testUserServiceLoginUserUsername(User $testUser)
    {
        $user = $this->userService->loginUser(
            $testUser->name,
            $this->password
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($testUser, $user);
        $this->assertEquals($testUser, $this->sessionService->getUser());

        return $testUser;
    }

    /**
     * @depends testUserServiceLoginUserUsername
     */
    public function testUserServiceLogout(User $testUser)
    {
        $this->userService->logoutUser();

        $this->assertFalse($this->sessionService->getUser());

        return $testUser;
    }

    /**
     * @depends testUserServiceLogout
     */
    public function testUserServiceLoginUserEmail(User $testUser)
    {
        $this->sessionService->start();

        $user = $this->userService->loginUser(
            $testUser->email,
            $this->password
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($testUser, $user);
        $this->assertEquals($testUser, $this->sessionService->getUser());

        return $testUser;
    }


    /**
     * @depends testUserServiceNewUser
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessageRegExp /Email Taken : \"(.*)\"/
     */
    public function testUserServiceEmailTaken(User $testUser)
    {
        $this->userService->newUser(
            $testUser->email,
            $this->faker->name,
            $this->faker->password,
            $this->faker->name
        );
    }

    /**
     * @depends testUserServiceNewUser
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessageRegExp /Username Taken : \"(.*)\"/
     */
    public function testUserServiceUsernameTaken(User $testUser)
    {
        $this->userService->newUser(
            $this->faker->safeEmail,
            $testUser->name,
            $this->faker->password,
            $this->faker->name
        );
    }

    /**
     * @expectedException \Tato\Exceptions\UserRegistrationException
     * @expectedExceptionMessageRegExp /Invalid User ID to delete : \"(.*)\"/
     */
    public function testUserServiceDeleteInvalidID()
    {
        $this->userService->deleteUser(-2);
        // @todo test a lookup.
    }
    
    /**
     * @depends testUserServiceNewUser
     */
    public function testUserServiceLogicalDelete(User $testUser)
    {
        /** @var $user User */

        $this->userService->deleteUser($testUser->user_id);
        $this->assertEquals("yes", $testUser->deleted);

        $user = User::search()->where("user_id", $testUser->user_id)->execOne();

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($testUser->email, $user->email);
        $this->assertEquals($testUser->name, $user->name);
        $this->assertEquals($testUser->pass, $user->pass);
        $this->assertEquals($testUser->display_name, $user->display_name);
        $this->assertEquals(User::STATE_IS_DELETED, $user->deleted);

        $user = $this->userService->getByID($testUser->user_id);
        $this->assertFalse($user);

        $user = $this->userService->getByID($testUser->user_id, true);
        $this->assertInstanceOf(User::class, $user);

        return $testUser;
    }

    /**
     * @depends testUserServiceLogicalDelete
     */
    public function testUserServiceTrueDelete(User $testUser)
    {
        /** @var $user User */
        $user = $this->userService->deleteUser($testUser->user_id, false);

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($testUser->email, $user->email);
        $this->assertEquals($testUser->name, $user->name);
        $this->assertEquals($testUser->pass, $user->pass);
        $this->assertEquals($testUser->display_name, $user->display_name);
        $this->assertEquals(User::STATE_IS_DELETED, $user->deleted);

        return $testUser;
    }

    /**
     * @depends testUserServiceTrueDelete
     */
    public function testUserServiceTrueDeleteCache(User $testUser)
    {
        // Fix for bug in Active Record See : https://github.com/Thruio/ActiveRecord/pull/26
        SearchIndex::getInstance()->wipe();
        /** @var $user User */
        $user = User::search()->where("user_id", $testUser->user_id)->execOne();
        $this->assertFalse($user);

        return $testUser;
    }


    /**
     * @depends testUserServiceTrueDeleteCache
     */
    public function testUserServiceNewUserDisplayNameShort(User $testUser)
    {
        /** @var $user User */
        $email = $this->faker->safeEmail;
        $name = $this->faker->userName;
        $pass = $this->faker->password;

        $user = $this->userService->newUser($email, $name, $pass, "ab");

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->name);
        $this->assertTrue(password_verify($pass, $user->pass));
        $this->assertEquals($name, $user->display_name);
        $this->assertEquals(User::STATE_IS_NOT_DELETED, $user->deleted);

        $user->delete();
    }
}
