<?php

namespace App\Utils\Twig\Tokens;

#[\Twig\Attribute\YieldReady]
class IsCookieActiveSeoNode extends \Twig\Node\Node {
    public function __construct($tests, int $lineno)
    {
        $nodes = ['tests' => $tests];

        parent::__construct($nodes, [], $lineno);
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