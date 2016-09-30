<?php

namespace PicoFeed\Http;

/**
 * Class Response
 *
 * @package PicoFeed\Http
 * @author  Frederic Guillot
 */
class Response
{
    protected $status = 0;
    protected $etag = '';
    protected $lastModified = '';
    protected $url = '';
    protected $body = '';

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
     * Get ETag
     *
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
     * Get Last-Modified header
     *
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
     * Set body
     *
     * @param  string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set HTTP status code
     *
     * @param  int $status
     * @return Response
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}
