<?php

namespace App\Utils\Twig\Tokens;

use App\Utils\Twig\Tokens\IsScriptActiveNode;

class IsScriptActive extends \Twig\TokenParser\AbstractTokenParser
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    private function findScript($name) {
        return in_array($name, $this->data["scripts"]);
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
                case 'endIsScriptActive':
                    $end = true;
                    break;

                default:
                    throw new SyntaxError(sprintf('Unexpected end of template. Twig was looking for the following tags "endIsScriptActive" to close the "isScriptActive" block started at line %d).', $lineno), $stream->getCurrent()->getLine(), $stream->getSourceContext());
            }
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        $isScriptActive = $this->findScript($name);

        return new IsScriptActiveNode(new \Twig\Node\Node($tests), $lineno, $this->getTag(), $isScriptActive);
    }

    public function getTag()
    {
        return 'isScriptActive';
    }

    public function decideIfEnd(\Twig\Token $token): bool
    {
        return $token->test(['endIsScriptActive']);
    }
}