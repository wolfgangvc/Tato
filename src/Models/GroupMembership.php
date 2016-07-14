<?php
namespace Tato\Models;

/**
 * Class GroupMembership
 * @package Tato\Models
 * @var $membership_id INTEGER
 * @var $user_id INTEGER
 * @var $group_id INTEGER
 */
class GroupMembership extends BaseModel
{
    protected $_table = "group_memberships";

    public $membership_id;
    public $user_id;
    public $group_id;

    /** @var Group */
    protected $_group;
    /** @var User */
    protected $_user;

    public function getGroup()
    {
        if (!$this->_group) {
            $this->_group = Group::search()->where("group_id", $this->group_id)->execOne();
        }
        return $this->_group;
    }

    public function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::search()->where("user_id", $this->user_id)->execOne();
        }
        return $this->_user;
    }
}
