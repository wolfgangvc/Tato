<?php

namespace Tato\Test;

use Tato\Services\SluggerService;

class SluggerServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SluggerService */
    protected $slugger;

    public function setUp()
    {
        parent::setUp();
        $this->slugger = new SluggerService();
    }

    public function testSluggerServiceAlphaNumeric()
    {
        $this->assertEquals("ljfh0d8gBNF", $this->slugger->slugify("ljfh::'0d8g(*(BNF)"));

        $this->assertEquals("spaces_are_invalid", $this->slugger->slugify("spaces are invalid"));
    }

    public function testSluggerServiceUmlauts()
    {
        $this->assertEquals("umlaut", $this->slugger->slugify("ü mlaü t"));
    }
}
