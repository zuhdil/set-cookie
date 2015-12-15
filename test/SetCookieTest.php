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
            array(
                'Foo=Bar%2FBaz; Domain=.foo.com; Path=/; Expires=Wed, 13 Jan 2021 22:23:01 GMT; Secure; HttpOnly',
                SetCookie::create('Foo')
                    ->withHttpOnly(true)
                    ->withValue('Bar/Baz')
                    ->withPath('/')
                    ->withSecure(true)
                    ->withExpires('Wed, 13 Jan 2021 22:23:01 GMT')
                    ->withDomain('.foo.com')
            ),
        );
    }

    /**
     * @dataProvider provideInvalidNameCases
     * @test
     */
    public function invalid_name_should_throws_InvalidArgumentException($name)
    {
        $this->setExpectedException('InvalidArgumentException');

        new SetCookie($name);
    }

    public function provideInvalidNameCases()
    {
        return array(
            array(''),
            array('=foo'),
            array(' foo'),
            array(',foo'),
            array(';foo'),
            array(' foo'),
            array("\tfoo"),
            array("\rfoo"),
            array("\nfoo"),
            array("\013foo"),
            array("\014foo"),
        );
    }

    /**
     * @dataProvider providePropertyValueCases
     * @test
     */
    public function modifying_property_should_return_SetCookie_object($property, $value)
    {
        $original = new SetCookie('foo');
        $method = 'with' . ucfirst($property);

        $this->assertInstanceOf('Zuhdil\SetCookie\SetCookie', $original->$method($value));
    }

    /**
     * @dataProvider providePropertyValueCases
     * @test
     */
    public function should_be_immutable($property, $value)
    {
        $original = new SetCookie('foo');
        $method = 'with' . ucfirst($property);

        $this->assertNotSame($original, $original->$method($value));
    }

    public function providePropertyValueCases()
    {
        return array(
            array('value', 'bar'),
            array('expires', 'Wed, 13 Jan 2021 22:23:01 GMT'),
            array('path', '/foo'),
            array('domain', '.foo.com'),
            array('secure', true),
            array('httpOnly', true),
        );
    }
}
