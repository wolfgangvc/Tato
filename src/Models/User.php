<?php
namespace Tato\Models;

use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Post
 * @package Tato\Models
 * @var $user_id INTEGER
 * @var $name TEXT
 * @var $display_name TEXT
 * @var $email TEXT
 * @var $verify_key = TEXT
 * @var $email_verified = ENUM("yes","no")
 * @var $pass TEXT
 * @var $created DATE
 * @var $deleted ENUM("yes","no")
 */
class User extends ActiveRecord
{
    protected $_table = "users";

    public $user_id;
    public $name;
    public $display_name;
    public $email;
    public $verify_key;
    public $email_verified = "no";
    public $pass;
    public $created;
    public $deleted = self::STATE_IS_NOT_DELETED;

    const STATE_IS_DELETED = "yes";
    const STATE_IS_NOT_DELETED = "no";

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
            $this->verify_key = sha1((string)rand(100000000, 999999999));
        }
        parent::save($automatic_reload);
    }
}
