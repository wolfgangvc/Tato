<?php

namespace Tato\Test\Mocks;

use Tato\Models;

class CommentServiceTestData
{
    /** @var Models\Post[] */
    public $postArray;
    /** @var Models\Comment[] */
    public $commentArray1;
    /** @var Models\Comment[] */
    public $commentArray2;

    /** @var Models\User */
    public $fakeUser1;
    /** @var Models\User */
    public $fakeUser2;
}
