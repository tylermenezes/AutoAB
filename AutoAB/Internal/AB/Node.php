<?php

namespace AutoAB\Internal\AB;


/**
 * Compiles an AB test node to PHP
 *
 * @author      Tyler Menezes <tylermenezes@gmail.com>
 * @copyright   Copyright (c) Tyler Menezes. Released under the Perl Artistic License 2.0.
 *
 * @package Jetpack\Internal\Twig_AB
 */
class Node extends \Twig_Node
{
    public $test_name = null;
    public $variants = null;

    /**
     * Creates a new Twig node object for the AB test
     *
     * @param array $test_name  The name of the test
     * @param array $variants   The sub-nodes which represent test variants
     * @param int   $line       The line number
     * @param null  $tag        The tag object
     */
    public function __construct($test_name, $variants, $line, $tag = null)
    {
        $this->test_name = $test_name;
        $this->variants = $variants;
        parent::__construct(array('variants' => new \Twig_Node($variants)), array('test_name' => $test_name), $line, $tag);
    }


    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler->write("{\n")->indent();

        // Generate the variants
        $compiler->write("\$ab_variants = [\n");
        $compiler->indent();
        foreach ($this->getNode('variants') as $k => $v) {
            $compiler->write("[\n");
            $compiler->indent()->write("'name' => '$k',\n");
            $compiler->write("'value' => function(){\n");
            $compiler->subcompile($v);
            $compiler->write("}\n");
            $compiler->outdent()->write("],\n");
        }
        $compiler->outdent()->write("];\n\n");


        $compiler->write("\$ab_selected_test = null;\n");

        // Generate the selector method
        $compiler->write("if (isset(\$_GET['__ab_" . $this->test_name . "'])) {\n");
        $compiler->indent()->write("foreach (\$ab_variants as \$ab_variant_iteration) {\n");
        $compiler->indent()->write("if (\$ab_variant_iteration['name'] === \$_GET['__ab_" . $this->test_name . "']) {\n");
        $compiler->indent()->write("\$ab_selected_test = \$ab_variant_iteration;\n");
        $compiler->write("break;\n");
        $compiler->outdent()->write("}\n");
        $compiler->outdent()->write("}\n");
        $compiler->outdent()->write("}\n\n");

        $compiler->write("if (!isset(\$ab_selected_test)) {\n");
        $compiler->write("\$ip = isset(\$_SERVER['HTTP_X_REAL_IP']) ? \$_SERVER['HTTP_X_REAL_IP'] : \$_SERVER['REMOTE_ADDR'];");
        $compiler->indent()->write("\$ab_random_seed = \$ip . count(\$ab_variants) . '" . $this->test_name . "';\n");
        $compiler->write("\$ab_hash_seed = substr(md5(\$ab_random_seed), 0, 6);\n");
        $compiler->write("\$ab_int_seed = intval(hexdec(\$ab_hash_seed));\n");
        $compiler->write("mt_srand(\$ab_int_seed);\n");
        $compiler->write("\$ab_selected_test_id = mt_rand(0, count(\$ab_variants) - 1);\n");
        $compiler->write("\$ab_selected_test = \$ab_variants[\$ab_selected_test_id];\n");
        $compiler->outdent()->write("}\n\n");


        // Set the global list of enrolled tests
        $compiler->write("global \$ab_enrolled_tests;\n");
        $compiler->write("if (!isset(\$ab_enrolled_tests)) {\n");
        $compiler->indent()->write("\$ab_enrolled_tests = [];\n");
        $compiler->outdent()->write("}\n");
        $compiler->write("\$ab_enrolled_tests['" . $this->test_name . "'] = \$ab_selected_test['name'];\n\n");

        // Execute the variant
        $compiler->write("\$ab_selected_test['value']();\n");

        $compiler->outdent()->write("}");
    }
}
