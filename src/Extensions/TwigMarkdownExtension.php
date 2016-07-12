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
        return Markdown::defaultTransform($markup);
    }

    public function markdownExtra($markup)
    {
        return MarkdownExtra::defaultTransform($markup);
    }

    public function getName()
    {
        return "markdown";
    }
}
