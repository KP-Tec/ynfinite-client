<?php

namespace Ypsolution\YnfinitePhpClient\utils\tokens;

use Ypsolution\YnfinitePhpClient\utils\tokens\IsCookieActiveNode;

class IsCookieActive extends \Twig\TokenParser\AbstractTokenParser
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function findCookie($name) {
        $cookie = null;

        foreach($this->data["cookies"]["available"] as $c) {
            if($c["alias"] === $name) {
                $cookie = $c;
                break;
            }
        }

        return in_array($cookie["_id"], $this->data["cookies"]["active"]);
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
                case 'endIsCookieActive':
                    $end = true;
                    break;

                default:
                    throw new SyntaxError(sprintf('Unexpected end of template. Twig was looking for the following tags "endIsCookieActive" to close the "isCookieActive" block started at line %d).', $lineno), $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        $isActiveCookie = $this->findCookie($name);

        return new IsCookieActiveNode(new \Twig\Node\Node($tests), $lineno, $this->getTag(), $isActiveCookie);
    }

    public function getTag()
    {
        return 'isCookieActive';
    }

    public function decideIfEnd(\Twig\Token $token): bool
    {
        return $token->test(['endIsCookieActive']);
    }
}