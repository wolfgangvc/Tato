<?php
namespace Tato\Models;

/**
 * Class User
 * @package Tato\Models
 * @var $user_id INTEGER
 * @var $name TEXT
 * @var $display_name TEXT
 * @var $email TEXT
 * @var $verify_key TEXT
 * @var $email_verified ENUM("yes","no")
 * @var $pass TEXT
 */
class User extends BaseModel
{
    protected $_table = "users";

    public $user_id;
    public $name;
    public $display_name;
    public $email;
    public $verify_key;
    public $email_verified = self::STATE_EMAIL_NOT_VERIFIED;
    public $pass;

    const STATE_EMAIL_VERIFIED = "yes";
    const STATE_EMAIL_NOT_VERIFIED = "no";
    
    /** @var Group[] */
    protected $_groupMemberships;

    /**
     * @return Group[]
     * @throws \Thru\ActiveRecord\Exception
     */
    public function getGroupMemberships()
    {
        if (!$this->_groupMemberships) {
            $this->_groupMemberships = array();
            $groups = GroupMembership::search()
                ->where("deleted", GroupMembership::STATE_IS_NOT_DELETED)
                ->where("user_id", $this->user_id)
                ->exec();
            /** @var $group GroupMembership*/
            foreach ($groups as $group) {
                $this->_groupMemberships[] = $group->getGroup();
            }
        }
        return $this->_groupMemberships;
    }


    public function hasPowerOver(User $user)
    {
        if (count($user->getGroupMemberships()) > 0) {
            /** @var $group Group*/
            if (count($this->getGroupMemberships()) > 0) {
                $group_ids = array();
                foreach ($this->getGroupMemberships() as $group) {
                    $group_ids[] = $group->group_id;
                }
                foreach ($user->getGroupMemberships() as $group) {
                    return $group->isChildOfAny($group_ids);
                }
            }
        }
        return false;
    }
}
