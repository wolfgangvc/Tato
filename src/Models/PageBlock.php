<?php
namespace Tato\Models;

/**
 * Class PageBlock
 * @package Tato\Models
 * @var $block_id INTEGER
 * @var $page_id INTEGER
 * @var $name TEXT
 * @var $content TEXT
 * @var $type ENUM("text","youtube","image")
 * @var $weight INTEGER
 */
class PageBlock extends BaseModel
{
    protected $_table = "page_blocks";

    public $block_id;
    public $page_id;
    public $name = "";
    public $content;
    public $type = self::CONTENT_TYPE_TEXT;
    public $weight;

    const CONTENT_TYPE_TEXT = "text";
    const CONTENT_TYPE_YOUTUBE = "youtube";
    const CONTENT_TYPE_IMAGE = "image";
}
