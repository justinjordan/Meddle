<?php

namespace Meddle\Services;

class Transpiler
{
    /**
     * Transpiles HTML document into PHP document
     *
     * @param string $templateContents
     * @return string PHP document.
     */
    public static function transpile(string $templateContents, string $cacheDir = null)
    {
        $templateContents = self::removePHP($templateContents);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->loadHTML($templateContents);

        /** Interpolate Tags */
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//text()");
        foreach ($nodes as $node) {
            $node->textContent = self::replaceTags($node->textContent);
        }

        $html = $document->saveHTML();
        $phpDocument = self::replacePseudoTags($html);

        return $phpDocument;
    }

    private static function replaceTags(string $text)
    {
        $text = preg_replace_callback("/{{([^}]*)}}/", function ($m) {
            $tagContents = trim($m[1]);
            $evaluated = self::evaluate($tagContents);
            return '{? echo '.$evaluated.'; ?}';
        }, $text);

        return $text;
    }

    private static function evaluate(string $input)
    {
        $output = $input;

        /** Plusses to dots */
        $output = preg_replace("/([^0-9\s][\s]*)([\+])/", '$1.', $output);

        /** Add dollar sign to variables */
        $output = preg_replace("/([a-z]+[a-z0-9]*)/i", "\$$1", $output);

        return $output;
    }

    private static function replacePseudoTags(string $input) {
        $output = $input;
        $output = str_replace('{?', '<?php', $output);
        $output = str_replace('?}', '?>', $output);
        return $output;
    }

    /**
     * Remove PHP tags for security
     *
     * @param string $templateContent
     * @return string Return new template
     */
    private static function removePHP(string $templateContent)
    {
        $templateContent = preg_replace("/(<\?)([\s\S]+)(\?>)/", '', $templateContent);
        return $templateContent;
    }
}