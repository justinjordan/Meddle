<?php

namespace Sxule\Meddle;

use DOMNode;
use DOMXPath;
use Exception;
use DOMDocument;
use Sxule\Meddle;
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
     * @var Meddle
     */
    private $meddle;

    /**
     * @var DOMDocument
     */
    private $document;

    public function __construct(Meddle $meddle)
    {
        $this->meddle = $meddle;
    }

    /**
     * Loads HTML document
     *
     * @param string $templateContents
     *
     * @return DOMDocument
     */
    public function loadDocument(string $templateContents)
    {
        $document = new DOMDocument('1.0', 'UTF-8');

        $templateContents = $this->removePHP($templateContents);
        $internalErrors = libxml_use_internal_errors(true);
        $document->loadHTML($templateContents);
        libxml_use_internal_errors($internalErrors);

        return $document;
    }

    /**
     * Transpiles HTML document into PHP document
     *
     * @param string $templateContents
     *
     * @return string PHP document.
     */
    public function transpile(string $templateContents)
    {
        // used later to strip html, head, and body tags
        $wasFragment = !preg_match('/<html/i', $templateContents);

        $this->document = $this->loadDocument($templateContents);

        // parse tags
        $this->findNodesByTag('mdl-extend', function ($node) use (&$wasFragment) {
            // get extension blocks
            $blocks = [];
            $this->findNodesByTag('mdl-block', function ($node) use (&$blocks) {
                $name = $node->getAttribute('name');
                $blocks[$name] = $node;
            }, $node);

            // load extended template
            $templatePath = $node->getAttribute('template');

            if ($templatePath[0] !== '/') {
                $templateDir = $this->meddle->getTemplateDir();
                if (!empty($templateDir)) {
                    $templatePath = $templateDir.'/'.$templatePath;
                }
            }

            if (!file_exists($templatePath)) {
                throw new Exception(ErrorMessagePool::get('transpilerMissingExtendTemplate', $templatePath));
            }

            $templateContents = file_get_contents($templatePath);
            $wasFragment = !preg_match('/<html/i', $templateContents);

            $this->document = $this->loadDocument($templateContents);

            // replace blocks
            $this->findNodesByTag('mdl-block', function($replacedBlock) use (&$blocks) {
                $name = $replacedBlock->getAttribute('name');

                if (!isset($blocks[$name])) {
                    return;
                }

                $importedBlock = $this->document->importNode($blocks[$name], true);
                $replacedBlock->parentNode->replaceChild($importedBlock, $replacedBlock);

                $this->removeWrappingElement($importedBlock);
            });
        });

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
        $this->forAllNodes($this->document, function ($node) {
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

        $html = $this->document->saveHTML();

        // get body only if template contents was a fragment
        if ($wasFragment && !preg_match('/<html[^>]*>/i', $templateContents)) {
            preg_match('/(<body[^>]*>)([\s\S]*)(<\/body>)/i', $html, $matches);
            $html = $matches[2];
        }
        $html = $this->replacePseudoTags($html);

        return $html;
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
     * Finds nodes with tag name and runs callback
     *
     * @param string    $tagName
     * @param callable  $callback
     *
     * @return void
     */
    private function findNodesByTag(string $tagName, callable $callback, DOMNode $context = null)
    {
        $xpath = new DOMXPath($this->document);
        $nodes = $xpath->query("//$tagName", $context);
        foreach ($nodes as $node) {
            $callback($node);
        }
    }

    /**
     * Finds nodes containing specified attribute and runs callback
     *
     * @param string    $attr
     * @param callable  $callback
     *
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

    private function removeWrappingElement(DOMNode $element)
    {
        $sibling = $element->firstChild;

        do {
            $next = $sibling->nextSibling;
            $element->parentNode->insertBefore($sibling, $element);
        } while($sibling = $next);

        $element->parentNode->removeChild($element);
    }
}
