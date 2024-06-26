<?php

namespace App\Utils\Twig\Tokens;

#[YieldReady]
class IsCookieActiveSeoNode extends \Twig\Node\Node {
    public function __construct(\Twig\Node\Node $tests, int $lineno, string $tag = null)
    {
        $nodes = ['tests' => $tests];

        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler->write('if(');
        $compiler->subcompile($this->getNode('tests')->getNode((string) 0))->raw(") {\n")->indent();
        $compiler->subcompile($this->getNode('tests')->getNode((string) 1));
        $compiler->write('}');
    }
}