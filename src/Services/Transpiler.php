<?php

namespace Meddle\Services;

use Meddle\Exceptions\SyntaxException;
use Meddle\ErrorHandling\ErrorMessagePool;

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
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($templateContents);
        libxml_use_internal_errors($internalErrors);

        /** Conditionals */
        $attr = 'mdl-if';
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//*[@$attr]");
        foreach ($nodes as $node) {
            $attrValue = $node->getAttribute($attr);
            $attrValue = self::evaluate($attrValue);
            $node->removeAttribute($attr);

            $openingTag = $document->createTextNode("{? if ($attrValue): ?}");
            $closingTag = $document->createTextNode("{? endif; ?}\n");

            $parent = $node->parentNode;
            $parent->insertBefore($openingTag, $node);
            if ($node->nextSibling) {
                $parent->insertBefore($closingTag, $node->nextSibling);
            } else {
                $parent->appendChild($closingTag);
            }
        }

        /** Foreach Loops */
        $attr = 'mdl-foreach';
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//*[@$attr]");
        foreach ($nodes as $node) {
            $attrValue = $node->getAttribute($attr);
            $attrValue = self::evaluate($attrValue);
            $node->removeAttribute($attr);

            $openingTag = $document->createTextNode("{? foreach ($attrValue): ?}");
            $closingTag = $document->createTextNode("{? endforeach; ?}\n");

            $parent = $node->parentNode;
            $parent->insertBefore($openingTag, $node);
            if ($node->nextSibling) {
                $parent->insertBefore($closingTag, $node->nextSibling);
            } else {
                $parent->appendChild($closingTag);
            }
        }

        /** For Loops */
        $attr = 'mdl-for';
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//*[@$attr]");
        foreach ($nodes as $node) {
            $attrValue = $node->getAttribute($attr);
            $attrValue = self::evaluate($attrValue);
            $node->removeAttribute($attr);

            $openingTag = $document->createTextNode("{? for ($attrValue): ?}");
            $closingTag = $document->createTextNode("{? endfor; ?}\n");

            $parent = $node->parentNode;
            $parent->insertBefore($openingTag, $node);
            if ($node->nextSibling) {
                $parent->insertBefore($closingTag, $node->nextSibling);
            } else {
                $parent->appendChild($closingTag);
            }
        }

        /** Mustache Tags */
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//text()");
        foreach ($nodes as $node) {
            $value = $node->textContent;
            $node->textContent = self::replaceTags($value);
        }

        $html = $document->saveHTML();
        $html = self::replacePseudoTags($html);

        return $html;
    }

    /**
     * Finds and replaces all mustache tags with PHP tags
     *
     * @param string $text
     * 
     * @throws SyntaxException
     * 
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
        
        /** Decode HTML Special Chars */
        $output = preg_replace_callback("/\{\?([^\?]*)\?\}/", function ($m) {
            return htmlspecialchars_decode($m[0]);
        }, $output);

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