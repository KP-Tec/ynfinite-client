<?php

namespace App\Utils\Twig\Tokens;

class IsCookieActiveNode extends \Twig\Node\Node {
    public function __construct(\Twig\Node\Node $tests, int $lineno, string $tag = null, $cookie = null)
    {
        $nodes = ['tests' => $tests];

        $this->cookie = $cookie;

        parent::__construct($nodes, [], $lineno, $tag);
    }
    
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if($this->cookie) {
            $compiler->subcompile($this->getNode('tests'));
        }
    }
}