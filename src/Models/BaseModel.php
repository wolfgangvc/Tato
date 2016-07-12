<?php
namespace Tato\Models;

use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Post
 * @package Tato\Models
 * @var $created DATE
 * @var $deleted ENUM("yes","no")
 */
class BaseModel extends ActiveRecord
{
    public $created;
    public $deleted = self::STATE_IS_NOT_DELETED;

    const STATE_IS_DELETED = "yes";
    const STATE_IS_NOT_DELETED = "no";

    public function save($automatic_reload = true)
    {
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
        }
        parent::save($automatic_reload);
    }

    public function logicalDelete()
    {
        $this->deleted = self::STATE_IS_DELETED;
        $this->save();
    }
}
