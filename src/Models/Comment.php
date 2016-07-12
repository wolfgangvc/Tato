<?php
namespace Tato\Models;

/**
 * Class Comment
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
    /** @var  Post */
    protected $_post;

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

    /**
     * @return false|Post
     * @throws \Thru\ActiveRecord\Exception
     */
    public function getPost()
    {
        if (!$this->_post) {
            $this->_post = Post::search()->where("post_id", $this->post_id)->execOne();
        }
        return $this->_post;
    }
}
