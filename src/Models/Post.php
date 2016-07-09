<?php
namespace Tato\Models;

use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Post
 * @package Tato\Models
 * @var $post_id INTEGER
 * @var $title TEXT
 * @var $body TEXT
 * @var $created DATE
 * @var $deleted ENUM("yes","no")
 */
class Post extends ActiveRecord
{
    protected $_table = "posts";

    public $post_id;
    public $title;
    public $body;
    public $created;
    public $deleted = "no";
}
