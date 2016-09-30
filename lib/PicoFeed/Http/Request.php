<?php

namespace PicoFeed\Http;

/**
 * Class Request
 *
 * @package PicoFeed\Http
 * @author  Frederic Guillot
 */
class Request
{
    protected $url           = '';
    protected $maxRedirect   = 5;
    protected $maxBodySize   = 2097152;
    protected $timeout       = 10;
    protected $proxyHostname = '';
    protected $proxyPort     = 3128;
    protected $proxyUsername = '';
    protected $proxyPassword = '';
    protected $username      = '';
    protected $password      = '';
    protected $userAgent     = 'PicoFeed <https://github.com/fguillot/picoFeed>';
    protected $lastModified  = '';
    protected $etag          = '';
    protected $passthrough   = false;

    /**
     * Set client timeout
     *
     * @param  int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Get client timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Set proxy hostname
     *
     * @param  string $proxyHostname
     * @return Request
     */
    public function setProxyHostname($proxyHostname)
    {
        $this->proxyHostname = $proxyHostname;
        return $this;
    }

    /**
     * Get proxy hostname
     *
     * @return string
     */
    public function getProxyHostname()
    {
        return $this->proxyHostname;
    }

    /**
     * Set proxy port
     *
     * @param  int $proxyPort
     * @return Request
     */
    public function setProxyPort($proxyPort)
    {
        $this->proxyPort = $proxyPort;
        return $this;
    }

    /**
     * Get proxy port
     *
     * @return int
     */
    public function getProxyPort()
    {
        return $this->proxyPort;
    }

    /**
     * Set proxy username
     *
     * @param  string $proxyUsername
     * @return $this
     */
    public function setProxyUsername($proxyUsername)
    {
        $this->proxyUsername = $proxyUsername;
        return $this;
    }

    /**
     * Get proxy username
     *
     * @return string
     */
    public function getProxyUsername()
    {
        return $this->proxyUsername;
    }

    /**
     * Get proxy password
     *
     * @param string $proxyPassword
     * @return $this
     */
    public function setProxyPassword($proxyPassword)
    {
        $this->proxyPassword = $proxyPassword;
        return $this;
    }

    /**
     * Get proxy password
     *
     * @return string
     */
    public function getProxyPassword()
    {
        return $this->proxyPassword;
    }

    /**
     * Set max redirects
     *
     * @param  int $maxRedirect
     * @return $this
     */
    public function setMaxRedirect($maxRedirect)
    {
        $this->maxRedirect = $maxRedirect;
        return $this;
    }

    /**
     * Get max redirect
     *
     * @return int
     */
    public function getMaxRedirect()
    {
        return $this->maxRedirect;
    }

    /**
     * Set max body size
     *
     * @param  int $maxBodySize
     * @return $this
     */
    public function setMaxBodySize($maxBodySize)
    {
        $this->maxBodySize = $maxBodySize;
        return $this;
    }

    /**
     * Get max body size
     *
     * @return int
     */
    public function getMaxBodySize()
    {
        return $this->maxBodySize;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Request
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Request
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set User-Agent header
     *
     * @param  string $userAgent
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * Get User-Agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Set ETag header
     *
     * @param  string $etag
     * @return $this
     */
    public function setEtag($etag)
    {
        $this->etag = $etag;
        return $this;
    }

    /**
     * @return string
     */
    public function getEtag()
    {
        return $this->etag;
    }

    /**
     * Set Last-Modified header
     *
     * @param  string $lastModified
     * @return $this
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * Set URL
     *
     * @param  string $url
     * @return $this
     */
    public function setUrl($url)
    {
        if (!preg_match('%^https?://%', $url)) {
            $url = 'http://'.$url;
        }

        $this->url = $url;
        return $this;
    }

    /**
     * Get Url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Enable passthrough mode
     *
     * @return Request
     */
    public function enablePassthroughMode()
    {
        $this->passthrough = true;
        return $this;
    }

    /**
     * Return true is body passtrough is enabled
     *
     * @return boolean
     */
    public function hasPassthroughModeActivated()
    {
        return $this->passthrough;
    }
}
