<?php

namespace Meddle\Services;

use Meddle\Exceptions\SyntaxException;
use Meddle\ErrorHandling\ErrorMessagePool;

class Transpiler
{
    /**
     * Reference to document
     *
     * @var DOMDocument
     */
    private $document;

    /**
     * Transpiles HTML document into PHP document
     *
     * @param string $templateContents
     * @return string PHP document.
     */
    public function transpile(string $templateContents)
    {
        $templateContents = $this->removePHP($templateContents);

        $document = new \DOMDocument('1.0', 'UTF-8');
        $this->document = $document;
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($templateContents);
        libxml_use_internal_errors($internalErrors);

        /** parse attributes */
        $this->findAndTranspileAttribute('mdl-if');
        $this->findAndTranspileAttribute('mdl-for');
        $this->findAndTranspileAttribute('mdl-foreach');

        /** parse mustache tags */
        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query("//text()");
        foreach ($nodes as $node) {
            $value = $node->textContent;
            $node->textContent = $this->replaceTags($value);
        }

        $html = $document->saveHTML();
        $html = $this->replacePseudoTags($html);

        return $html;
    }

    private function findAndTranspileAttribute(string $attr) {
        /**
         * Define open/close tags
         */
        $open   = '';
        $close  = '';
        switch ($attr) {
            case 'mdl-if':
                $open   = "{? if (%s): ?}";
                $close  = "{? endif; ?}";
                break;
            case 'mdl-for':
                $open   = "{? for (%s): ?}";
                $close  = "{? endfor; ?}";
                break;
            case 'mdl-foreach':
                $open   = "{? foreach (%s): ?}";
                $close  = "{? endforeach; ?}";
                break;
        }

        $xpath = new \DOMXPath($this->document);
        $nodes = $xpath->query("//*[@$attr]");
        foreach ($nodes as $node) {
            $attrValue = $node->getAttribute($attr);
            $attrValue = $this->decorateVariables($attrValue);
            $node->removeAttribute($attr);

            $openingTag = $this->document->createTextNode(sprintf($open, $attrValue));
            $closingTag = $this->document->createTextNode("$close\n");

            $parent = $node->parentNode;
            $parent->insertBefore($openingTag, $node);
            if ($node->nextSibling) {
                $parent->insertBefore($closingTag, $node->nextSibling);
            } else {
                $parent->appendChild($closingTag);
            }
        }
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
    private function replaceTags(string $text)
    {
        $text = preg_replace_callback("/{{([^}]*)}}/", function ($m) {
            $tagContents = trim($m[1]);
            $evaluated = $this->decorateVariables($tagContents);
            return '{? echo '.$evaluated.'; ?}';
        }, $text);

        return $text;
    }

    /**
     * Locates variables and adds $ to them.
     *
     * @param string $input Meddle statement
     *
     * @return string PHP statement
     */
    private function decorateVariables(string $input)
    {
        $inQuotes = false;
        $escaped = false;
        $closer = null;

        $variableIndex = null;
        $foundVariables = [];

        /** find variables */
        for ($index = 0, $len = strlen($input); $index < $len; $index++) {
            $letter = $input[$index];

            switch ($letter) {
                case '"':
                case "'":
                    if (!$inQuotes) {
                        /** start quote block */
                        $inQuotes = true;
                        $closer = $letter;
                    } elseif ($inQuotes && $letter === $closer && !$escaped) {
                        /** end quote block */
                        $inQuotes = false;
                        $closer = null;
                    }
                    break;
                
                default:
                    /**
                     * part of variable name
                     */
                    if (preg_match('/[a-z0-9_]/i', $letter) && !$inQuotes && $variableIndex === null) {
                        $variableIndex = $index;
                    }

                    $nextIndex = $index + 1;
                    if ($variableIndex !== null && ($nextIndex >= $len || !preg_match('/[a-z0-9_]/i', $input[$nextIndex]))) {
                        /** get variable name */
                        $variable = substr($input, $variableIndex, $nextIndex - $variableIndex);

                        /** check variable name validity */
                        if (preg_match('/[a-z_][a-z0-9_]*/i', $variable)) {
                            $foundVariables[] = $variable;
                        }

                        /** reset index */
                        $variableIndex = null;
                    }
                    break;
            }

            /** escape next character */
            $escaped = $letter === '\\';
        }

        /** add $ to variables */
        $skip = ['true', 'false', 'null', 'as'];
        $output = preg_replace_callback('/\w+/', function ($m) use ($skip, $foundVariables) {
            $op = $m[0];
            if (!in_array($op, $skip) && in_array($op, $foundVariables)) {
                $op = '$' . $op;
            }
            return $op;
        }, $input);

        return $output;
    }

    /**
     * Replaces {? ?} blocks with <?php ?>
     *
     * @param string $input
     *
     * @return void
     */
    private function replacePseudoTags(string $input) {
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
     * This prevents users from manually injecting their own scripts into the
     * templates. Since templates are transpiled into a PHP file, without this
     * they'd be able to write any code they wanted as long as it's wrapped in
     * `<? ?>` or `{? ?}` tags.
     *
     * @param string $templateContent
     *
     * @return string Return new template
     */
    private function removePHP(string $templateContent)
    {
        /** Remove user PHP tags */
        $templateContent = preg_replace("/(<\?)([\s\S]+)(\?>)/", '', $templateContent);

        /** Remove user pseudo tags */
        $templateContent = preg_replace("/({\?)([\s\S]+)(\?})/", '', $templateContent);

        return $templateContent;
    }
}