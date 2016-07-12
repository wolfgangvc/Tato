<?php
namespace Tato\Models;

/**
 * Class User
 * @package Tato\Models
 * @var $user_id INTEGER
 * @var $name TEXT
 * @var $display_name TEXT
 * @var $email TEXT
 * @var $verify_key = TEXT
 * @var $email_verified = ENUM("yes","no")
 * @var $pass TEXT
 */
class User extends BaseModel
{
    protected $_table = "users";

    public $user_id;
    public $name;
    public $display_name;
    public $email;
    public $verify_key;
    public $email_verified = self::STATE_EMAIL_NOT_VERIFIED;
    public $pass;

    const STATE_EMAIL_VERIFIED = "yes";
    const STATE_EMAIL_NOT_VERIFIED = "no";
}
