<?php
namespace Tato\Models;

/**
 * Class Page
 * @package Tato\Models
 * @var $page_id INTEGER
 * @var $user_id INTEGER
 * @var $group_id INTEGER
 * @var $title TEXT
 * @var $body TEXT
 */
class Page extends BaseModel
{
    protected $_table = "pages";

    public $page_id;
    public $user_id;
    public $group_id = 0;
    public $title;
    public $name;

    protected $_blocks;

    public function getBlocks($includeDeleted = false)
    {
        if (!$this->_blocks) {
            $search = PageBlock::search();
            if (!$includeDeleted) {
                $search->where("deleted", PageBlock::STATE_IS_NOT_DELETED);
            }
            $this->_blocks = PageBlock::search()
                ->where("page_id", $this->page_id)
                ->order("weight", "ASC")
                ->exec();
        }
        return $this->_blocks;
    }

    public function getBlockCount()
    {
        return count($this->getBlocks());
    }
}
