<?php
namespace Zuhdil\SetCookie;

use Psr\Http\Message\ResponseInterface;

class SetCookie
{
    const HEADER_NAME = 'Set-Cookie';

    private $name;
    private $value;
    private $expires;
    private $path;
    private $domain;
    private $secure;
    private $httpOnly;

    public function __construct($name, $value = null, $expires = null, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        $this->name = $name;
        $this->value = $value;
        $this->expires = $this->resolveExpires($expires);
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = (bool) $secure;
        $this->httpOnly = (bool) $httpOnly;
    }

    public static function create($name, $value = null, $expires = null, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        return new self($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    public function __toString()
    {
        $parts = array(urlencode($this->name) . '=' . urlencode($this->value));

        if ($this->domain) {
            $parts[] = 'Domain='.$this->domain;
        }

        if ($this->path) {
            $parts[] = 'Path='.$this->path;
        }

        if ($this->expires) {
            $parts[] = 'Expires='.gmdate('D, d M Y H:i:s T', $this->expires);
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        return implode('; ', $parts);
    }

    public function withValue($value)
    {
        $clone = clone($this);
        $clone->value = $value;

        return $clone;
    }

    public function withExpires($expires)
    {
        $clone = clone($this);
        $clone->expires = $this->resolveExpires($expires);

        return $clone;
    }

    public function withPath($path)
    {
        $clone = clone($this);
        $clone->path = $path;

        return $clone;
    }

    public function withDomain($domain)
    {
        $clone = clone($this);
        $clone->domain = $domain;

        return $clone;
    }

    public function withSecure($secure)
    {
        $clone = clone($this);
        $clone->secure = (bool) $secure;

        return $clone;
    }

    public function withHttpOnly($httpOnly)
    {
        $clone = clone($this);
        $clone->httpOnly = (bool) $httpOnly;

        return $clone;
    }

    public function apply(ResponseInterface $respose)
    {
        return $respose->withAddedHeader(self::HEADER_NAME, (string) $this);
    }

    private function resolveExpires($time)
    {
        if (is_null($time) || is_numeric($time)) {
            return $time;
        }

        if (is_object($time) && method_exists($time, 'getTimestamp')) {
            return $time->getTimestamp();
        }

        return strtotime($time);
    }
}
