<?php
namespace Zuhdil\SetCookie;

class SetCookie
{
    private $name;
    private $value;
    private $expires;
    private $path;
    private $domain;
    private $secure;
    private $httpOnly;

    public function __construct($name, $value = null, $expires = null, $path = null, $domain = null, $secure = false, $httpOnly = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->expires = $expires;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
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
            $parts[] = 'Expires='.$this->expires;
        }

        if ($this->secure) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly) {
            $parts[] = 'HttpOnly';
        }

        return implode('; ', $parts);
    }
}
