<?php

namespace App\Utils\Twig\Tokens;

class IsScriptActiveNode extends \Twig\Node\Node {
    public $script;
    
    public function __construct(\Twig\Node\Node $tests, int $lineno, string $tag = null, $script = null)
    {
        $nodes = ['tests' => $tests];

        $this->script = $script;

        parent::__construct($nodes, [], $lineno, $tag);
    }
    
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if($this->script) {
            $compiler->subcompile($this->getNode('tests'));
        }
    }
}