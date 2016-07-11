<?php
namespace Tato\Models;

use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Post
 * @package Tato\Models
 * @var $comment_id INTEGER
 * @var $post_id INTEGER
 * @var $user_id INTEGER
 * @var $title TEXT
 * @var $body TEXT
 * @var $created DATE
 * @var $deleted ENUM("yes","no")
 */
class Comment extends ActiveRecord
{
    protected $_table = "comments";

    public $comment_id;
    public $post_id;
    public $user_id;
    public $title;
    public $body;
    public $created;
    public $deleted = "no";

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
        }
        parent::save($automatic_reload);
    }
}
