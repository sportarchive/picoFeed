<?php

namespace PicoFeed\Http\Adapter;

use PicoFeed\Http\Request;
use PicoFeed\Http\Response;

/**
 * Class BaseAdapter
 *
 * @package PicoFeed\Http\Adapter
 * @author  Frederic Guillot
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * Return true if the HTTP status code is a redirection
     *
     * @access protected
     * @param  integer  $code
     * @return boolean
     */
    protected function isRedirection($code)
    {
        return $code == 301 || $code == 302 || $code == 303 || $code == 307;
    }
}
