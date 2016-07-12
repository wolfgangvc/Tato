<?php

namespace Tato\Test;

use Tato\Services\UserService;
use Tato\Models\User;
use Thru\ActiveRecord\SearchIndex;

class UserServiceTest extends BaseTest
{
    /** @var  UserService */
    protected $userService;

    public function setUp()
    {
        parent::setUp();
        $this->userService = new UserService();
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
        $pass = $this->faker->password;
        $dName = $this->faker->name;

        $testUser = $this->userService->newUser(
            $email,
            $name,
            $pass,
            $dName
        );

        $this->assertInstanceOf(User::class, $testUser);

        $testUser = User::search()->where("user_id", $testUser->user_id)->execOne();

        $this->assertInstanceOf(User::class, $testUser);

        $this->assertEquals(strtolower($email), $testUser->email);
        $this->assertEquals(strtolower($name), $testUser->name);
        $this->assertTrue(password_verify($pass, $testUser->pass));
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
    public function testUserDeleteInvalidID()
    {
        $this->userService->deleteUser(-2);
        // @todo test a lookup.
    }
    
    /**
     * @depends testUserServiceNewUser
     */
    public function testUserLogicalDelete(User $testUser)
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
     * @depends testUserLogicalDelete
     */
    public function testUserTrueDelete(User $testUser)
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
     * @depends testUserTrueDelete
     */
    public function testUserTrueDeleteCache(User $testUser)
    {
        // Fix for bug in Active Record See : https://github.com/Thruio/ActiveRecord/pull/26
        SearchIndex::getInstance()->wipe();
        /** @var $user User */
        $user = User::search()->where("user_id", $testUser->user_id)->execOne();
        $this->assertFalse($user);

        return $testUser;
    }


    /**
     * @depends testUserTrueDeleteCache
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
