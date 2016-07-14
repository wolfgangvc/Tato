<?php
namespace Tato\Models;

use Thru\ActiveRecord\ActiveRecord;

/**
 * Class BaseModel
 * @package Tato\Models
 * @var $deleted ENUM("yes","no")
 * @var $created DATE
 * @var $deleted_on DATE
 */
abstract class BaseModel extends ActiveRecord
{
    public $created;
    public $deleted = self::STATE_IS_NOT_DELETED;
    public $deleted_on;

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
        $this->deleted_on = date("Y-m-d H:i:s");
        $this->save();
    }
}
