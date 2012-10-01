AutoAB
======
A really simple way to run AB tests.

Introduction
------------
> AB testing is a pretty simple concept which isn't very hard to do. AutoAB doesn't solve any
> difficult problem, it's just there to present a small library so you don't have to implement
> AB testing yourself.

Requirements
------------
 * PHP &ge; 5.3.5

Use
===
Running Tests
-------------
Running a test with AutoAB is pretty simple. For exmaple, let's say we want to AB test a hero
graphic on our homepage. That would look something like this:

	<div id="hero">
        <?php \AutoAB\AB::test('main_page_hero',
                                   'cloud', TPL_DIR . '/parts/hero/cloud.php',
                                   'social', TPL_DIR . '/parts/hero/social.php'
                               ); ?>
    </div>

We just created a test called `main_page_hero`, consisting of two possible versions, called
`cloud` and `social`. AutoAB detected that `TPL_DIR . '/parts/hero/*.php'` were files, and
included one of the two for us automatically.

We're not limited to just including files, or just running two tests, though. Here's an
example of AB testing the page tagline:

    <h1>AutoAB</h1>
    <span id="tagline">
        <?php \AutoAB\AB::test('tagline',
                                  'fast', 'The fastest way to AB test!',
                                  'easy', 'The easiest way to AB test!,
                                  'cool', 'The coolest way to AB test!
                              ); ?>
    </span>

Even though we still passed a string, AutoAB noticed it wasn't a file, and included it as
text instead.

With closures in PHP 5.3, we can also AB-test functions!

    $rating_function = \AutoAB\AB::test('rating_system',
                                            'hot', $rating_hot,
                                            'best', $rating_best
                                        );

    $rated_comments = $rating_function($comments);

Testing the Tests
-----------------
AutoAB will, well, automatically enroll everyone in an AB test, including you. So how do you
make sure all your AB variants work?

By adding `__ab_testname=variant` to the query string, you can force AutoAB to enroll you in
a specific test. e.g., to force 'best' sorting in the previous example, we'd just visit
`http://example.org/xyz/comments?__ab_rating_system=best`

You can also see a list of all AB tests run on a page with the paramater `__ab_show_all`.

It's strongly recommended that you disable this in production! To do so, just set
`\AutoAB\AB::$enable_override = FALSE`.

Getting Results
---------------
AB testing is pretty useless without being able to measure the results. With AutoAB, this is
pretty simple. To get a list of the variants the user is seeing, just call
`\AutoAB\AB::get_enrollment()`. You'll get back an associative array of `test_name` =>
`variant`.

You can easily tie this in with your existing metrics software. Here's an example of telling
Mixpanel what tests the user is currently enrolled in:

    <script type="text/javascript">
        mixpanel.register({
        <?php $i = 0; foreach (\AutoAB\AB::get_enrollment() as $test_name=>$variant) : $i++;?>
            "<?=$test_name?>": "<?=$variant?>"<?php if ($i !== count(\AutoAB\AB::get_enrollment())) echo ","; ?>

        <?php endforeach; ?>
        });
    </script>

(You can also call `\AutoAB\AB::get_enrollment($test_name)` to get the result of a single test.