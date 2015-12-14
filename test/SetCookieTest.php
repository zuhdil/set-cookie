<?php
namespace Zuhdil\SetCookie;

class SetCookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSetCookieToStringCases
     * @test
     */
    public function should_render_formatted_cookie_string($expectedString, SetCookie $subject)
    {
        $this->assertEquals($expectedString, (string) $subject);
    }

    public function provideSetCookieToStringCases()
    {
        return array(
            array(
                'foo=',
                new SetCookie('foo'),
            ),
            array(
                'foo=bar',
                new SetCookie('foo', 'bar'),
            ),
            array(
                'Foo=Bar%2FBaz; Expires=Wed, 13 Jan 2021 22:23:01 GMT',
                new SetCookie('Foo', 'Bar/Baz', 'Wed, 13 Jan 2021 22:23:01 GMT'),
            ),
            array(
                'Foo=Bar%2FBaz; Domain=.foo.com; Path=/quux; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Secure; HttpOnly',
                new SetCookie('Foo', 'Bar/Baz', 'Wed, 13 Jan 2021 22:23:01 GMT', '/quux', '.foo.com', true, true),
            ),
        );
    }
}
