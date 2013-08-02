<?php

namespace AutoAB\Internal\AB;


/**
 * Parses an AB test into nodes
 *
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes. Released under the Perl Artistic License 2.0.
 *
 * @package Jetpack\Internal\Twig_AB
 */
class Parser extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();
        $test_name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        $test_name = preg_replace("/[^a-zA-Z0-9]+/", "", $test_name);

        $variants = [];

        // Find the first variant name, and discard content before it.
        $this->parser->subparse(array($this, 'decideIfVariant'));

        // Parse the available variants
        $end = false;
        while (!$end) {
            switch ($stream->next()->getValue()) {
                case 'variant':
                    $name = $stream->expect(\Twig_Token::NAME_TYPE)->getValue();
                    $stream->expect(\Twig_Token::BLOCK_END_TYPE);
                    $body = $this->parser->subparse(array($this, 'decideIfVariant'));

                    $name = preg_replace("/[^a-zA-Z0-9]+/", "", $name);

                    $variants[$name] = $body;
                    break;

                case 'endab':
                    $end = true;
                    break;

                default:
                    throw new \Twig_Error_Syntax(sprintf('ab block started on line %d was not closed.)', $lineno),
                        $stream->getCurrent()->getLine(),
                        $stream->getFilename());
            }
        }

        if (count($variants) < 1) {
            throw new \Twig_Error_Syntax(sprintf('ab block started on line %d had no variants.)', $lineno),
                $stream->getCurrent()->getLine(),
                $stream->getFilename());
        }


        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new Node($test_name, $variants, $token->getLine(), $this->getTag());
    }

    public function decideIfVariant(\Twig_Token $token)
    {
        return $token->test(array('variant', 'endab'));
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'ab';
    }
}
