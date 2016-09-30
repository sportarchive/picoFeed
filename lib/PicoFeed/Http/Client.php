<?php

namespace PicoFeed\Http;

use LogicException;
use PicoFeed\Http\Adapter\AdapterInterface;
use PicoFeed\Http\Adapter\CurlAdapter;

/**
 * Class Client
 *
 * @package PicoFeed\Http
 * @author  Frederic Guillot
 */
class Client
{
    /**
     * Get Client adapter
     *
     * @return AdapterInterface
     */
    public static function getAdapter()
    {
        if (function_exists('curl_init')) {
            return new CurlAdapter();
        } elseif (ini_get('allow_url_fopen')) {
            return new StreamAdapter();
        }

        throw new LogicException('You must have "allow_url_fopen=1" or curl extension installed');
    }
}
