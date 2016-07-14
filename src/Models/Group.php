<?php
namespace Tato\Models;

/**
 * Class Group
 * @package Tato\Models
 * @var $group_id INTEGER
 * @var $group_name INTEGER
 * @var $default = ENUM("yes","no")
 */
class Group extends BaseModel
{
    protected $_table = "groups";

    public $group_id;
    public $group_name;
    public $deleted = self::GROUP_IS_NOT_DEFAULT;
    
    const GROUP_IS_DEFAULT = "yes";
    const GROUP_IS_NOT_DEFAULT = "no";

    protected $_parents;
    public function getParents()
    {
        if (!$this->_parents) {
            $this->_parents = array();
            $parents = GroupMembership::search()
                ->where("deleted", GroupMembership::STATE_IS_NOT_DELETED)
                ->where("child_id", $this->group_id)
                ->exec();
            /** @var $parent GroupOwnership*/
            foreach ($parents as $parent) {
                $this->_parents[] = $parent->getParent();
            }
        }
        return $this->_parents;
    }

    protected $_children;
    public function getChildren()
    {
        if (!$this->_children) {
            $this->_children = array();
            $children = GroupMembership::search()
                ->where("deleted", GroupMembership::STATE_IS_NOT_DELETED)
                ->where("parent_id", $this->group_id)
                ->exec();
            /** @var $child GroupOwnership*/
            foreach ($children as $child) {
                $this->_children[] = $child->getChild();
            }
        }
        return $this->_children;
    }

    /**
     * @param int $group_id
     * @return bool
     */
    public function isChildOf(int $group_id)
    {
        return $this->isChildOfAny([$group_id]);
    }

    /**
     * @param int[] $group_ids
     * @return bool
     */
    public function isChildOfAny(array $group_ids)
    {
        $parents = $this->getParents();
        /** @var $parent Group*/
        foreach ($parents as $parent) {
            if (in_array($parent->group_id, $group_ids)) {
                return true;
            } elseif ($parent->isChildOfAny($group_ids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param int $group_id
     * @return bool
     */
    public function isParentOf(int $group_id)
    {
        return $this->isParentOfAny([$group_id]);
    }

    /**
     * @param int[] $group_ids
     * @return bool
     */
    public function isParentOfAny(array $group_ids)
    {
        $children = $this->getChildren();
        /** @var $child Group*/
        foreach ($children as $child) {
            if (in_array($child->group_id, $group_ids)) {
                return true;
            } elseif ($child->isParentOfAny($group_ids)) {
                return true;
            }
        }
        return false;
    }
}
