<?php
namespace Tato\Services;

class SluggerService
{

    public function __construct()
    {
    }

    public function slugify(string $text)
    {
        foreach ($this->getForbiddenCharacters() as $forbidden => $replacement) {
            $text = str_replace($forbidden, $replacement, $text);
        }

        $text = preg_replace("/[^A-Za-z0-9 ]/", '', $text);
        $text = str_replace(" ", "_", $text);

        return $text;
    }

    protected function getForbiddenCharacters()
    {
        return [
            "À" => "A",
            "Á" => "A",
            "Â" => "A",
            "Ã" => "A",
            "Ä" => "A",
            "Å" => "A",
            "Æ" => "AE",
            "Ç" => "C",
            "È" => "E",
            "É" => "E",
            "Ê" => "E",
            "Ë" => "E",
            "Ì" => "I",
            "Í" => "I",
            "Î" => "I",
            "Ï" => "I",
            "Ð" => "D",
            "Ñ" => "N",
            "Ò" => "O",
            "Ó" => "O",
            "Ô" => "O",
            "Õ" => "O",
            "Ö" => "O",
            "Ø" => "O",
            "Š" => "S",
            "Ù" => "U",
            "Ú" => "U",
            "Û" => "U",
            "Ü" => "U",
            "Ý" => "Y",
            "Ÿ" => "Y",
            "ß" => "SS",
            "à" => "a",
            "á" => "a",
            "â" => "a",
            "ã" => "a",
            "ä" => "a",
            "å" => "a",
            "æ" => "ae",
            "ç" => "c",
            "è" => "e",
            "é" => "e",
            "ê" => "e",
            "ë" => "e",
            "ì" => "I",
            "í" => "I",
            "î" => "I",
            "ï" => "I",
            "ð" => "o",
            "ñ" => "n",
            "ò" => "o",
            "ó" => "o",
            "ô" => "o",
            "õ" => "o",
            "ö" => "o",
            "ø" => "o",
            "š" => "s",
            "ù" => "u",
            "ú" => "u",
            "û" => "u",
            "ü" => "u",
            "ý" => "y",
            "ÿ" => "y"
        ];
    }
}
