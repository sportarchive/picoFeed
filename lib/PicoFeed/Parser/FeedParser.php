<?php

namespace PicoFeed\Parser;

use DOMXPath;
use PicoFeed\Parser\Exception\UnsupportedFeedFormatException;
use PicoFeed\Processor\ItemPostProcessor;
use PicoFeed\Processor\ProcessorFactory;

/**
 * Class FeedParser
 *
 * @package PicoFeed\Parser
 * @author  Frederic Guillot
 */
class FeedParser
{
    const ATOM  = 'Atom';
    const RSS20 = 'Rss20';
    const RSS10 = 'Rss10';
    const RSS92 = 'Rss92';
    const RSS91 = 'Rss91';

    /**
     * Get parser object
     *
     * @param  string                    $parserType
     * @param  ItemPostProcessor|null    $itemPostProcessor
     * @param  DateParser|null           $dateParser
     * @return AtomParser|Rss10Parser|Rss20Parser|Rss91Parser|Rss92Parser
     * @throws UnsupportedFeedFormatException
     */
    public static function getParser($parserType, ItemPostProcessor $itemPostProcessor = null, DateParser $dateParser = null)
    {
        $dateParser = $dateParser ?: new DateParser();
        $itemPostProcessor = $itemPostProcessor ?: ProcessorFactory::getItemPostProcessor();

        switch ($parserType) {
            case self::ATOM:
                return new AtomParser($dateParser, $itemPostProcessor);
            case self::RSS20:
                return new Rss20Parser($dateParser, $itemPostProcessor);
            case self::RSS10:
                return new Rss10Parser($dateParser, $itemPostProcessor);
            case self::RSS92:
                return new Rss92Parser($dateParser, $itemPostProcessor);
            case self::RSS91:
                return new Rss91Parser($dateParser, $itemPostProcessor);
        }

        throw new UnsupportedFeedFormatException('Feed format not supported');
    }

    /**
     * Get parser type
     *
     * @param  string $data
     * @return string
     */
    public static function getParserType($data)
    {
        $queries = array(
            self::ATOM  => '//feed',
            self::RSS20 => '//rss[@version="2.0"]',
            self::RSS92 => '//rss[@version="0.92"]',
            self::RSS91 => '//rss[@version="0.91"]',
            self::RSS10 => '//rdf',
        );

        $dom = XmlParser::getHtmlDocument($data);
        $xpath = new DOMXPath($dom);

        foreach ($queries as $parserType => $query) {
            $nodes = $xpath->query($query);

            if ($nodes->length === 1) {
                return $parserType;
            }
        }

        return '';
    }
}
