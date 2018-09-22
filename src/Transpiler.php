<?php

namespace Sxule\Meddle;

use Sxule\Meddle\Parser;
use Sxule\Meddle\Transpiler\IfAttribute;
use Sxule\Meddle\Transpiler\ForAttribute;
use Sxule\Meddle\Transpiler\ForeachAttribute;
use Sxule\Meddle\Exceptions\SyntaxException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;
use DOMXPath;

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
        $this->findNodesWithAttr('mdl-if', function ($node) {
            IfAttribute::transpileNode($node);
        });
        $this->findNodesWithAttr('mdl-for', function ($node) {
            ForAttribute::transpileNode($node);
        });
        $this->findNodesWithAttr('mdl-foreach', function ($node) {
            ForeachAttribute::transpileNode($node);
        });

        /** parse mustache tags */
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//text()");
        foreach ($nodes as $node) {
            $value = $node->textContent;
            $node->textContent = $this->replaceTags($value);
        }

        $html = $document->saveHTML();
        $html = $this->replacePseudoTags($html);

        return $html;
    }

    /**
     * Finds nodes containing specified attribute and runs callback
     *
     * @param string    $attr
     * @param callable  $callback
     * @return void
     */
    private function findNodesWithAttr(string $attr, callable $callback)
    {
        $xpath = new DOMXPath($this->document);
        $nodes = $xpath->query("//*[@$attr]");
        foreach ($nodes as $node) {
            $callback($node);
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
            $evaluated = Parser::decorateVariables($tagContents);
            return '{? echo '.$evaluated.'; ?}';
        }, $text);

        return $text;
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

    private function formatSyntax(string &$attr, string $code)
    {
        switch ($attr) {
            case 'mdl-for':
                if (preg_match('/([a-z_][a-z0-9_]*)[\s]*in[\s]*([a-z_][a-z0-9_]*)/i', $code)) {
                    list($alias, $variable) = preg_split('/[\s]*in[\s]*/i', $code);
                    $attr = 'mdl-foreach';
                    $code = "$variable as $alias";
                }
                break;
        }

        return $code;
    }
}