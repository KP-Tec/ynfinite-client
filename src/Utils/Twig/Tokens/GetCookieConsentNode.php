<?php

namespace App\Utils\Twig\Tokens;

#[YieldReady]
class GetCookieConsentNode extends \Twig\Node\Node {

    public $twig;
    public $active;
    public $cookie;

    public function __construct(\Twig\Node\Node $tests, int $lineno, string $tag = null, $twig, $cookie = array())
    {
        $nodes = ['tests' => $tests];

        $this->active = $cookie["active"];
        $this->cookie = $cookie["cookie"];
        $this->twig = $twig;

        parent::__construct($nodes, [], $lineno, $tag);
    }
    
    public function compile(\Twig\Compiler $compiler)
    {
        $compiler->addDebugInfo($this);
        if($this->active) {
            $compiler->subcompile($this->getNode('tests'));
        }
        else {
            
            // $cookieConsent = $this->twig->render("yn/module/cookieManager/consent.twig", array("cookie" => $this->cookie));
            
            $compiler->write('$this->loadTemplate(\'yn/module/consentManager/consent.twig\')->display(')
            ->repr(array("cookie" => $this->cookie))
            ->write(');');
        }
    }
}