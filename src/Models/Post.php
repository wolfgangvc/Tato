<?php
namespace Tato\Models;

use Michelf\Markdown;
use Michelf\MarkdownExtra;
use Symfony\Component\Yaml\Dumper;

/**
 * Class Post
 * @package Tato\Models
 * @var $post_id INTEGER
 * @var $user_id INTEGER
 * @var $title TEXT
 * @var $body TEXT
 */
class Post extends BaseModel
{
    protected $_table = "posts";

    public $post_id;
    public $user_id;
    public $title;
    public $body;

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

    public function getMarkdown()
    {
        return MarkdownExtra::defaultTransform($this->body);
    }
}
