# AutoAB
A really simple way to run AB tests using Twig.

## Introduction

> AB testing is a pretty simple concept which isn't very hard to do. AutoAB doesn't solve any
> difficult problem, it's just there to present a small library so you don't have to implement
> AB testing yourself.

## Requirements
 * PHP &ge; 5.4

# Use

## Running Tests

Running a test with AutoAB is pretty simple. For exmaple, let's say we want to AB test some text on our homepage. That would look like:

	<div id="hero">
        {% ab homepage_hero_text %}
            {% variant cloud %}
                <h1>Store your data in the cloud!</h1>
                <h2>It's the future!</h2>
            {% variant never_loose %}
                <h1>Never lose your data again!</h1>
                <h2>Your data will be stored in our systems!</h2>
        {% endab %}
    </div>

We just created a test called `homepage_hero_text`, consisting of two possible versions, called
`cloud` and `never_lose`.

(As with any Twig tag, you can use other tags inside your AB tests.)

## Testing the Tests

AutoAB will, well, automatically enroll everyone in an AB test, including you. So how do you
make sure all your AB variants work?

By adding `__ab_testname=variant` to the query string, you can force AutoAB to enroll you in
a specific test. e.g., to force the cloud varient from the previous example, we'd just visit
`http://example.org/?__ab_homepage_hero_text=cloud`

## Getting Results

AB testing is pretty useless without being able to measure the results. With AutoAB, this is
pretty simple. To get a list of the variants the user is seeing, just use the `{{ ab_running }}` global.

You can easily tie this in with your existing metrics software. You can do this using a standard Twig for-loop, or, if you're using
Mixpanel (or some other client-side analytics program which takes JSON), you can just print the result of `{{ ab_running | raw }}`
directly.

    <script type="text/javascript">
        mixpanel.register({{ ab_running | raw }});
    </script>

(You can also call `{{ ab_running.test_name }}` to get the result of a single test.
