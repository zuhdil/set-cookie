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

    /**
     * @dataProvider provideMultipleExpiresTimeTypeCases
     * @test
     */
    public function expires_should_accept_multipe_time_type($expected, $name, $expires)
    {
        $this->assertEquals($expected, (string) SetCookie::create($name)->withExpires($expires));
    }

    public function provideMultipleExpiresTimeTypeCases()
    {
        $cases = array(
            array('foo=', 'foo', null, 'should ignore null'),
            array('foo=; Expires=Wed, 13 Jan 2021 22:23:01 GMT', 'foo', strtotime('Wed, 13 Jan 2021 22:23:01 GMT'), 'should accept int timestamp'),
            array('foo=', 'foo', 0, 'should ignore zero'),
            array('foo=; Expires=Wed, 13 Jan 2021 22:23:01 GMT', 'foo', 'Wed, 13 Jan 2021 22:23:01 GMT', 'should accept time string'),
            array('foo=', 'foo', '', 'should ignore string'),
            array('foo=; Expires=Wed, 13 Jan 2021 22:23:01 GMT', 'foo', new \DateTime('Wed, 13 Jan 2021 22:23:01 GMT'), 'should accept DateTime object'),
        );

        if (version_compare(PHP_VERSION, '5.5.0', '>=')) {
            $cases[] = array('foo=; Expires=Wed, 13 Jan 2021 22:23:01 GMT', 'foo', new \DateTimeImmutable('Wed, 13 Jan 2021 22:23:01 GMT'), 'should accept DateTimeImmutable object');
        }

        return $cases;
    }

    /**
     * @test
     */
    public function should_be_able_to_render_into_response_header()
    {
        $cookie = SetCookie::create('foo', 'bar');
        $responseSpy = $this->prophesize('Psr\Http\Message\ResponseInterface');

        $cookie->apply($responseSpy->reveal());

        $responseSpy->withAddedHeader(SetCookie::HEADER_NAME, 'foo=bar')
            ->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function apply_into_response_header_should_return_response_object()
    {
        $cookie = SetCookie::create('foo', 'bar');
        $responseSpy = $this->prophesize('Psr\Http\Message\ResponseInterface');

        $responseSpy->withAddedHeader(SetCookie::HEADER_NAME, 'foo=bar')
            ->willReturn($responseSpy->reveal());

        $result = $cookie->apply($responseSpy->reveal());

        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $result);
    }
}
