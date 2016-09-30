<?php

namespace PicoFeed\Processor;

/**
 * Class ProcessorFactory
 *
 * @package PicoFeed\Processor
 * @author  Frederic Guillot
 */
class ProcessorFactory
{
    /**
     * Get default item post processor
     *
     * @static
     * @return ItemPostProcessor
     */
    public static function getItemPostProcessor()
    {
        $itemPostProcessor = new ItemPostProcessor();
        $itemPostProcessor->register(new ContentGeneratorProcessor());
        $itemPostProcessor->register(new ContentFilterProcessor());
        return $itemPostProcessor;
    }
}
