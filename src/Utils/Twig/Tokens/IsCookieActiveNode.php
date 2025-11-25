<?php

namespace App\Utils\Twig\Tokens;

#[\Twig\Attribute\YieldReady]
class IsCookieActiveNode extends \Twig\Node\Node {
    public $cookie;
    
    public function __construct($tests, int $lineno, $cookie = null)
    {
        $nodes = ['tests' => $tests];

        $this->cookie = $cookie;

        parent::__construct($nodes, [], $lineno);
    }
    
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if($this->cookie) {
            $compiler->subcompile($this->getNode('tests'));
        }
    }
}