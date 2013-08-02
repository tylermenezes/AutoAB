<?php

namespace AutoAB;

require_once(implode(DIRECTORY_SEPARATOR, ['Internal', 'require.php']));

use \AutoAB\Internal;


/**
 * 
 * 
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes. Released under the Perl Artistic License 2.0.
 *
 * @package Jetpack\Internal\TwigAB
 */
class AB extends \Twig_Extension {
    public function getName()
    {
        return "autoab";
    }

    public function getGlobals()
    {
        return array(
            'ab_running' => new Internal\Running()
        );
    }

    public function getTokenParsers()
    {
        return array(new Internal\AB\Parser());
    }
}
