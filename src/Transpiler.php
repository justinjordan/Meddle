<?php

namespace Sxule\Meddle;

use DOMXPath;
use DOMDocument;
use Sxule\Meddle\Parser;
use Sxule\Meddle\Transpiler\IfAttribute;
use Sxule\Meddle\Transpiler\ForAttribute;
use Sxule\Meddle\Transpiler\ForeachAttribute;
use Sxule\Meddle\Transpiler\IgnoreAttribute;
use Sxule\Meddle\Exceptions\SyntaxException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;

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
     * @param string $templateContents  Template HTML
     * @param array  $components        Array of component classes
     *
     * @return string PHP document.
     */
    public function transpile(string $templateContents, array $components = [])
    {
        $templateContents = $this->removePHP($templateContents);

        $document = new DOMDocument('1.0', 'UTF-8');
        $this->document = $document;

        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($templateContents);
        libxml_use_internal_errors($internalErrors);

        // parse attributes
        $this->findNodesWithAttr('mdl-if', function ($node) {
            IfAttribute::transpileNode($node);
        });
        $this->findNodesWithAttr('mdl-for', function ($node) {
            ForAttribute::transpileNode($node);
        });
        $this->findNodesWithAttr('mdl-foreach', function ($node) {
            ForeachAttribute::transpileNode($node);
        });

        // parse mustache tags
        $this->forAllNodes($document, function ($node) {
            switch ($node->nodeName) {
                case '#text':
                    $value = $node->textContent;
                    $node->textContent = $this->replaceTags($value);
                    break;
                default:
                    foreach ($node->attributes as $attr) {
                        $attr->value = $this->replaceTags($attr->value);
                    }
                    break;
            }
        });

        // remove mdl-ignore attributes
        $this->findNodesWithAttr('mdl-ignore', function ($node) {
            IgnoreAttribute::transpileNode($node);
        });

        // Register custom components to prevent DOM error
        $this->registerComponents($document, $components);

        $html = $document->saveHTML();

        // get body only if template contents was a fragment
        if (!preg_match('/<html[^>]*>/i', $templateContents)) {
            preg_match('/(<body[^>]*>)([\s\S]*)(<\/body>)/i', $html, $matches);
            $html = $matches[2];
        }
        $html = $this->replacePseudoTags($html);

        return $html;
    }

    private function registerComponents(DOMDocument $document, array $components)
    {
        foreach ($components as $tagName => $className) {
            // if (!class_exists($className)) {
            //     continue;
            // }

            if (is_numeric($tagName)) {
                $parts = explode('\\', $className);
                $name = array_pop($parts);
                $words = preg_split("/(?=[A-Z])/", $name);
                $words = array_filter($words);

                $tagName = strtolower(implode('-', $words));
            }

            $this->findNodesByName($tagName, function ($node) use ($className) {
                new $className($node);
            });
        }
    }

    /**
     * Run callback for all nodes in the document
     * 
     * @param DOMDocument $document
     * @param callable    $callable
     * 
     * @return void
     */
    private function forAllNodes(DOMDocument $document, callable $callback)
    {
        $xpath = new DOMXPath($document);
        $nodes = $xpath->query("//node()[not(ancestor::*[@mdl-ignore])]");
        foreach ($nodes as $node) {
            $callback($node);
        }
    }

    /**
     * finds nodes by name and runs callback
     *
     * @param string    $name
     * @param callable  $callback
     *
     * @return void
     */
    private function findNodesByName(string $tagName, callable $callback)
    {
        $nodes = $this->document->getElementsByTagName($tagName);
        foreach ($nodes as $node) {
            $callback($node);
        }
    }

    /**
     * finds nodes containing specified attribute and runs callback
     *
     * @param string    $attr
     * @param callable  $callback
     *
     * @return void
     */
    private function findNodesWithAttr(string $attr, callable $callback)
    {
        $xpath = new domxpath($this->document);
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
            $evaluated = Parser::parse($tagContents);
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
        
        // Decode HTML Special Chars
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
        // Remove user PHP tags
        $templateContent = preg_replace("/(<\?)([\s\S]+)(\?>)/", '', $templateContent);

        // Remove user pseudo tags
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
