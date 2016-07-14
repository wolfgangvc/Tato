<?php
namespace Tato\Models;

/**
 * Class GroupOwnership
 * @package Tato\Models
 * @var $ownership_id INTEGER
 * @var $parent_id INTEGER
 * @var $child_id INTEGER
 */
class GroupOwnership extends BaseModel
{
    protected $_table = "group_ownerships";

    public $ownership_id;
    public $parent_id;
    public $child_id;

    /** @var Group */
    protected $_parent;
    /** @var User */
    protected $_child;

    public function getParent()
    {
        if (!$this->_parent) {
            $this->_parent = Group::search()->where("group_id", $this->parent_id)->execOne();
        }
        return $this->_parent;
    }

    public function getChild()
    {
        if (!$this->_child) {
            $this->_child = Group::search()->where("group_id", $this->child_id)->execOne();
        }
        return $this->_child;
    }
}
