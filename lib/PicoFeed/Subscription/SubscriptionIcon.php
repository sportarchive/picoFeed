<?php

namespace PicoFeed\Subscription;

/**
 * Class SubscriptionIcon
 *
 * @package PicoFeed\Subscription
 * @author  Frederic Guillot
 */
class SubscriptionIcon
{
    /**
     * Valid types for favicon (supported by browsers).
     *
     * @var array
     */
    private $types = array(
        'image/png',
        'image/gif',
        'image/x-icon',
        'image/jpeg',
        'image/jpg',
        'image/svg+xml'
    );

    /**
     * Icon binary content.
     *
     * @var string
     */
    private $blob = '';

    /**
     * Icon content type.
     *
     * @var string
     */
    private $contentType = '';

    /**
     * Icon URL
     *
     * @var string
     */
    private $url = '';

    /**
     * Create object instance
     *
     * @static
     * @access public
     * @return SubscriptionIcon
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Set icon binary blob
     *
     * @param  string $blob
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->blob = $blob;
        return $this;
    }

    /**
     * Get the icon binary content
     *
     * @return string
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * Set icon content type
     *
     * @param  string $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Get the icon file type (available only after the download).
     *
     * @return string
     */
    public function getContentType()
    {
        foreach ($this->types as $type) {
            if (strpos($this->contentType, $type) === 0) {
                return $type;
            }
        }

        return 'image/x-icon';
    }

    /**
     * Get data URI (http://en.wikipedia.org/wiki/Data_URI_scheme).
     *
     * @return string
     */
    public function getDataUri()
    {
        if (empty($this->blob)) {
            return '';
        }

        return sprintf(
            'data:%s;base64,%s',
            $this->getType(),
            base64_encode($this->blob)
        );
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
     * Get URL
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
