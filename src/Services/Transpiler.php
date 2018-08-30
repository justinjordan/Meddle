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
    public static function transpile(string $templateContents)
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

    /**
     * Finds and replaces all mustache tags with PHP tags
     *
     * @param string $text
     * @return string Returns replaced text
     */
    private static function replaceTags(string $text)
    {
        $text = preg_replace_callback("/{{([^}]*)}}/", function ($m) {
            $tagContents = trim($m[1]);
            $evaluated = self::evaluate($tagContents);
            return '{? echo '.$evaluated.'; ?}';
        }, $text);

        return $text;
    }

    /**
     * Converts Meddle syntax to PHP
     *
     * @param string $input Meddle statement
     * @return string PHP statement
     */
    private static function evaluate(string $input)
    {
        $output = $input;

        /**
         * Add $ to functions to prevent user from calling
         * unauthorized or undefined functions.
         */
        $output = preg_replace_callback("/([\$]*[a-z_][a-z0-9]*)\(/i", function ($matches) {
            $op = $matches[0];
            if ($op[0] !== '$') {
                $op = '$' . $op;
            }
            return $op;
        }, $output);

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