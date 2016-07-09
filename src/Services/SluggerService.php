<?php
namespace Tato\Services;

class SluggerService
{

    public function __construct()
    {

    }

    public function slugify(string $text)
    {
        $text = preg_replace("/[^A-Za-z0-9 ]/", '', $text);
        $text = str_replace(" ","_",$text);

        return $text;
    }
}