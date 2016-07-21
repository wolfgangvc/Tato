<?php

namespace Tato\Services;

use Tato\Models\Page;
use Tato\Models\PageBlock;

class PageService
{

    /** @var SessionService */
    protected $sessionService;

    public function __construct($sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function getByName(string $name, $includeDeleted = false)
    {
        $search = Page::search();
        if (!$includeDeleted) {
            $search->where("deleted", Page::STATE_IS_NOT_DELETED);
        }
        return $search->where("name", $name)->execOne();
    }

    public function getByID(int $id, $includeDeleted = false)
    {
        $search = Page::search();
        if (!$includeDeleted) {
            $search->where("deleted", Page::STATE_IS_NOT_DELETED);
        }
        return $search->where("page_id", $id)->execOne();
    }
    
    public function newPage(string $name, string $title)
    {
        $sUser = $this->sessionService->getUser();
        if ($sUser) {
            $page = $this->getByName($name);
            if (!$page) {
                $page = new Page();
                $page->name = $name;
                $page->title = $title;
                $page->user_id = $sUser->user_id;
                $page->save();
                return $page;
            }
        }
    }

    public function newPageBlockPageID(
        int $page_id,
        string $name,
        string $content,
        $type = PageBlock::CONTENT_TYPE_TEXT
    ) {
    
        $page = $this->getByID($page_id);
        if ($page) {
            return $this->newPageBlock($page, $name, $content, $type);
        }
        return false;
    }

    public function newPageBlock(
        Page $page,
        string $name,
        string $content,
        $type = PageBlock::CONTENT_TYPE_TEXT
    ) {
    
        $block = new PageBlock();
        $block->page_id = $page->page_id;
        $block->name = $name;
        $block->content = $content;
        $block->type = $type;
        $block->weight = $page->getBlockCount() + 1;
        $block->save();
        return $block;
    }

    public function deletePageByName(string $name, $logicalDelete = true)
    {
        $page = $this->getByName($name);
        if ($page) {
            return $this->deletePage($page, $logicalDelete);
        }
    }

    public function deletePageByID(int $page_id, $logicalDelete = true)
    {
        $page = $this->getByID($page_id);
        if ($page) {
            return $this->deletePage($page, $logicalDelete);
        }
    }

    public function deletePage(Page $page, $logicalDelete = true)
    {
        if ($logicalDelete) {
            $page->logicalDelete();
        } else {
            $page->delete();
        }
        return $page;
    }
}
