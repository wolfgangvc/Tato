<?php

namespace Tato\Extensions;

use Michelf\Markdown;
use Michelf\MarkdownExtra;

class TwigMarkdownExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('markdown', array($this, 'markdown')),
            new \Twig_SimpleFilter('markdownExtra', array($this, 'markdownExtra'))
        );
    }

    public function markdown($markup)
    {
        $parser = new Markdown();
        $parser->no_markup = true;
        return $parser->transform($markup);
    }

    public function markdownExtra($markup)
    {
        $parser = new MarkdownExtra();
        $parser->no_markup = true;
        return $parser->transform($markup);
    }

    public function getName()
    {
        return "markdown";
    }
}
