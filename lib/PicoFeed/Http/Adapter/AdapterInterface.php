<?php

namespace PicoFeed\Http\Adapter;

use PicoFeed\Http\Request;
use PicoFeed\Http\Response;

/**
 * Interface AdapterInterface
 *
 * @package PicoFeed\Http\Adapter
 * @author  Frederic Guillot
 */
interface AdapterInterface
{
    /**
     * Execute HTTP request
     *
     * @param  Request $request
     * @return Response
     */
    public function execute(Request $request);
}
