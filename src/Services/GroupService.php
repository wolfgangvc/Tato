<?php
namespace Tato\Services;

use Tato\Exceptions\AddUserToGroupException;
use Tato\Exceptions\DeletePostException;
use Tato\Exceptions\EditPostException;
use Tato\Exceptions\NewPostException;
use Tato\Exceptions\RemoveUserFromGroupException;
use Tato\Models\Group;
use Tato\Models\GroupMembership;
use Tato\Models\GroupOwnership;
use Tato\Models\Post;
use Tato\Models\User;

class GroupService
{
    /** @var UserService */
    protected $userService;
    /** @var SessionService */
    protected $sessionService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getByID(int $group_id, $includeDeleted = false)
    {
        $search = Group::search();
        if (!$includeDeleted) {
            $search->where("deleted", Group::STATE_IS_NOT_DELETED);
        }
        return $search
            ->where("group_id", $group_id)
            ->execOne();
    }


    public function addUserIDToGroupID(int $user_id, int $group_id)
    {
        $user = $this->userService->getByID($user_id);
        if ($user instanceof User) {
            return $this->addUserToGroupID(
                $user,
                $group_id
            );
        }
        return false;
    }

    public function addUserIDToGroup(int $user_id, Group $group)
    {
        $user = $this->userService->getByID($user_id);
        if ($user instanceof User) {
            return $this->addUserToGroup(
                $user,
                $group
            );
        }
        return false;
    }

    public function addUserToGroupID(User $user, int $group_id)
    {
        $group = $this->getByID($group_id);
        if ($group instanceof Group) {
            return $this->addUserToGroup($user, $group);
        }
        return false;
    }

    public function addUserToGroup(User $user, Group $group)
    {
        $groupMembership = GroupMembership::search()
            ->where("deleted", GroupMembership::STATE_IS_NOT_DELETED)
            ->where("group_id", $group->group_id)
            ->where("user_id", $user->user_id)
            ->execOne();
        if ($groupMembership instanceof GroupMembership) {
            throw new AddUserToGroupException("User already in group");
        }
        $groupMembership = new GroupMembership();
        $groupMembership->group_id = $group->group_id;
        $groupMembership->user_id = $user->user_id;
        $groupMembership->save();
        return true;
    }

    public function removeUserFromGroup(User $user, Group $group)
    {
        $groupMembership = GroupMembership::search()
            ->where("deleted", GroupMembership::STATE_IS_NOT_DELETED)
            ->where("group_id", $group->group_id)
            ->where("user_id", $user->user_id)
            ->execOne();
        if (!$groupMembership instanceof GroupMembership) {
            throw new RemoveUserFromGroupException("User not in group");
        }
        $groupMembership->logicalDelete();
        return true;
    }

    public function getDefaultGroups($includeDeleted = false)
    {
        $search = Group::search();
        if (!$includeDeleted) {
            $search->where("deleted", Group::STATE_IS_NOT_DELETED);
        }
        return $search
            ->where("default", Group::GROUP_IS_DEFAULT)
            ->exec();
    }
}
