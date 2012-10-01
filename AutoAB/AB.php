<?php

namespace AutoAB;

class AB
{
    private static $tests = array();

    /**
     * Enables the use of __ab_ paramaters in the query string.
     * @var boolean
     */
    public static $enable_override = TRUE;

    /**
     * Runs an AB test
     * @param  string $test_id ID of the test to run
     * @params mixed           Variants to use for the test in the form of "name", "variant", "name", "variant", ...
     * @return mixed           Selected test -- result of an included file if the selected test was the path of a file which exists,
     *                         otherwise the selected object
     */
    public static function test($test_id)
    {
        $config = func_get_args();
        array_shift($config); // Remove $test_id from the args


        // Because the arguments are staggered ('name', 'variant', 'name', 'varient', ...), we'll build an associative array of 'name' => 'variant'
        // here to be more useful:
        if (count($config) % 2 !== 0) {
            throw new \Exception('Paramaters must be of form "name", "variant", "name", "variant", ...');
        }

        $options = array();
        for ($i = 0; $i < count($config); $i += 2) {
            $name = $config[$i];
            $value = $config[$i + 1];

            $options[$name] = $value;
        }


        if (self::$enable_override && isset($_REQUEST['__ab_' . $test_id])) {
            // Set the test to what the user specified
            $selected_id = $_REQUEST['__ab_' . $test_id];
        } else {
            // Pick the test at random

            // Now generate the random seed. We want the current user to always see the same option for any given test, so the seed will be based on the
            // IP and test ID.
            $seed = $_SERVER['REMOTE_ADDR'] . $test_id;
            $random_id = self::seeded_random_number($seed, 0, count($options) - 1);
            $keys = array_keys($options);
            $selected_id = $keys[$random_id];
        }

        $selected = $options[$selected_id];

        self::$tests[$test_id] = $selected_id;

        // If the user asks for a list of AB test spots, show a box here!
        if (isset($_REQUEST['__ab_show_all'])) {
            echo '<span style="border-bottom:1px dotted #aaa;padding:4px;margin:0 5px">' . $test_id . ':';
            foreach ($options as $name=>$val) {
                echo '<a style="background:#000;color:#fff;padding:3px;text-decoration:none;margin:0 0 0 5px;" href="?__ab_' . urlencode($test_id) . '=' . urlencode($name) .'">' . htmlentities($name) . '</a>';
            }
            echo '</span>';
        }

        // Magic happens here!
        if (is_string($selected) && file_exists($selected)) {
            return require($selected); // If it's a file which exists, include it.
        } else {
            // Default to returning the selected object
            return $selected;
        }
    }

    /**
     * Gets a list of tests the user is enrolled in
     * @return array key=>value pair of tests
     */
    public static function get_enrollment($test_name = NULL)
    {
        if (isset($test_name)) {
            return self::$tests[$test_name];
        } else {
            return self::$tests;
        }
    }

    /**
     * Gets a random number from the seed
     * @param  string $seed The seed to use
     * @param  int    $min  Min number
     * @param  int    $max  Max number
     * @return int          Random number
     */
    private static function seeded_random_number($seed, $min, $max)
    {
        $hash_seed = md5($seed);
        $hash_seed = substr($hash_seed, 0, 6); // srand takes a signed int, so we need to make sure the value of hexdec is an ingeger. On 32-bit platforms,
                                          // that means we have 31 bits, so we'll just get the first 6 characters of the hash. This isn't great, but
                                          // it's just an AB test so whatever.

        $int_seed = intval(hexdec($hash_seed));

        mt_srand($int_seed); // Now we seed the random number generator...
        return mt_rand($min, $max); // ...and generate the number!
    }
}
