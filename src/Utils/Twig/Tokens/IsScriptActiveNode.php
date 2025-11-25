<?php

namespace App\Utils\Twig\Tokens;

#[\Twig\Attribute\YieldReady]
class IsScriptActiveNode extends \Twig\Node\Node {
    public $script;
    
    public function __construct(\Twig\Node\Node $tests, int $lineno, $script = null)
    {
        $nodes = ['tests' => $tests];

        $this->script = $script;

        parent::__construct($nodes, [], $lineno);
    }
    
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if($this->script) {
            $compiler->subcompile($this->getNode('tests'));
        }
    }
}