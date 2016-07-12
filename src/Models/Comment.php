<?php
namespace Tato\Models;

/**
 * Class Post
 * @package Tato\Models
 * @var $comment_id INTEGER
 * @var $post_id INTEGER
 * @var $user_id INTEGER
 * @var $title TEXT
 * @var $body TEXT
 */
class Comment extends BaseModel
{
    protected $_table = "comments";

    public $comment_id;
    public $post_id;
    public $user_id;
    public $title;
    public $body;

    /** @var  User */
    protected $_user;

    /**
     * @return false|User
     * @throws \Thru\ActiveRecord\Exception
     */
    public function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::search()->where('user_id', $this->user_id)->execOne();
        }
        return $this->_user;
    }
}
