<?php

namespace App\Utils\Twig\Tokens;

use App\Utils\Twig\Tokens\GetCookieConsentNode;

class GetCookieConsent extends \Twig\TokenParser\AbstractTokenParser
{

    private $data;
    public $twig;

    public function __construct($data, $twig)
    {
        $this->data = $data;
        $this->twig = $twig;
    }

    private function findCookie($name) {
        $cookie = null;
        $active = false;

        foreach($this->data["cookies"]["available"] as $c) {
            if($c["alias"] === $name) {
                $cookie = $c;
                break;
            }
        }

        if(!is_null($cookie) && in_array($cookie["_id"], $this->data["cookies"]["active"])) {
            $active = true;
        }

        return array("cookie" => $cookie, "active" => $active);
    }

    public function parse(\Twig\Token $token)
    {

        $parser = $this->parser;
        $stream = $parser->getStream();
        

        $lineno = $token->getLine();
        $name = $stream->expect(\Twig\Token::STRING_TYPE)->getValue();
        $stream = $this->parser->getStream();
        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);
        $body = $this->parser->subparse([$this, 'decideIfEnd']);
        $tests = [$body];

        $end = false;
        while (!$end) {
            switch ($stream->next()->getValue()) {
                case 'endGetCookieConsent':
                    $end = true;
                    break;

                default:
                    throw new SyntaxError(sprintf('Unexpected end of template. Twig was looking for the following tags "endGetCookieConsent" to close the "getCookieConsent" block started at line %d).', $lineno), $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3); 
        $foundCookie = $this->findCookie($name);

        return new GetCookieConsentNode(new \Twig\Node\Nodes($tests), $lineno, $this->twig, $foundCookie);
    }

    public function getTag()
    {
        return 'getCookieConsent';
    }

    public function decideIfEnd(\Twig\Token $token): bool
    {
        return $token->test(['endGetCookieConsent']);
    }
}