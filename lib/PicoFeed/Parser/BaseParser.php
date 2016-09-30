<?php

namespace PicoFeed\Parser;

use PicoFeed\Processor\ItemPostProcessor;
use SimpleXMLElement;
use PicoFeed\Client\Url;
use PicoFeed\Helper\EncodingHelper;
use PicoFeed\Filter\Filter;
use PicoFeed\Logging\Logger;

/**
 * Base parser class.
 *
 * @package PicoFeed\Parser
 * @author  Frederic Guillot
 */
abstract class BaseParser implements ParserInterface
{
    /**
     * DateParser object.
     *
     * @var DateParser
     */
    protected $dateParser;

    /**
     * Hash algorithm used to generate item id, any value supported by PHP, see hash_algos().
     *
     * @var string
     */
    private $hashAlgo = 'sha256';

    /**
     * Feed content (XML data).
     *
     * @var string
     */
    protected $content = '';

    /**
     * Fallback url.
     *
     * @var string
     */
    protected $fallbackUrl = '';

    /**
     * XML namespaces supported by parser.
     *
     * @var array
     */
    protected $namespaces = array();

    /**
     * XML namespaces used in document.
     *
     * @var array
     */
    protected $usedNamespaces = array();

    /**
     * Item Post Processor instance
     *
     * @access private
     * @var ItemPostProcessor
     */
    private $itemPostProcessor = null;

    /**
     * BaseParser constructor.
     *
     * @param DateParser $dateParser
     * @param ItemPostProcessor $itemPostProcessor
     */
    public function __construct(DateParser $dateParser, ItemPostProcessor $itemPostProcessor)
    {
        $this->dateParser = $dateParser;
        $this->itemPostProcessor = $itemPostProcessor;
    }

    /**
     * Set Hash algorithm used for id generation.
     *
     * @param  string $hashAlgo Algorithm name
     * @return $this
     */
    public function setHashAlgo($hashAlgo)
    {
        $this->hashAlgo = $hashAlgo ?: $this->hashAlgo;
        return $this;
    }

    /**
     * Parse a feed
     *
     * @param  string $data
     * @param  string $fallbackUrl
     * @param  string $httpEncoding
     * @throws MalformedXmlException
     * @return Feed
     */
    public function execute($data, $fallbackUrl = '', $httpEncoding = '')
    {
        Logger::setMessage(get_called_class().': begin parsing');

        $this->fallbackUrl = $fallbackUrl;
        $xmlEncoding = XmlParser::getEncodingFromXmlTag($data);

        // Strip XML tag to avoid multiple encoding/decoding in the next XML processing
        $this->content = Filter::stripXmlTag($data);

        // Encode everything in UTF-8
        Logger::setMessage(get_called_class().': HTTP Encoding "'.$httpEncoding.'" ; XML Encoding "'.$xmlEncoding.'"');
        $this->content = EncodingHelper::convert($this->content, $xmlEncoding ?: $httpEncoding);

        $xml = XmlParser::getSimpleXml($this->content);

        if ($xml === false) {
            Logger::setMessage(get_called_class().': Applying XML workarounds');
            $this->content = Filter::normalizeData($this->content);
            $xml = XmlParser::getSimpleXml($this->content);

            if ($xml === false) {
                Logger::setMessage(get_called_class().': XML parsing error');
                Logger::setMessage(XmlParser::getErrors());
                throw new MalformedXmlException('XML parsing error');
            }
        }

        $this->usedNamespaces = $xml->getNamespaces(true);
        $xml = $this->registerSupportedNamespaces($xml);

        $feed = new Feed();

        $this->findFeedUrl($xml, $feed);
        $this->checkFeedUrl($feed);

        $this->findSiteUrl($xml, $feed);
        $this->checkSiteUrl($feed);

        $this->findFeedTitle($xml, $feed);
        $this->findFeedDescription($xml, $feed);
        $this->findFeedLanguage($xml, $feed);
        $this->findFeedId($xml, $feed);
        $this->findFeedDate($xml, $feed);
        $this->findFeedLogo($xml, $feed);
        $this->findFeedIcon($xml, $feed);

        foreach ($this->getItemsTree($xml) as $entry) {
            $entry = $this->registerSupportedNamespaces($entry);

            $item = new Item();
            $item->xml = $entry;
            $item->namespaces = $this->usedNamespaces;

            $this->findItemAuthor($xml, $entry, $item);

            $this->findItemUrl($entry, $item);
            $this->checkItemUrl($feed, $item);

            $this->findItemTitle($entry, $item);
            $this->findItemContent($entry, $item);

            // Id generation can use the item url/title/content (order is important)
            $this->findItemId($entry, $item, $feed);
            $this->findItemDate($entry, $item, $feed);
            $this->findItemEnclosure($entry, $item, $feed);
            $this->findItemLanguage($entry, $item, $feed);

            $this->itemPostProcessor->execute($feed, $item);
            $feed->items[] = $item;
        }

        Logger::setMessage(get_called_class().PHP_EOL.$feed);

        return $feed;
    }

    /**
     * Check if the feed url is correct.
     *
     * @param Feed $feed Feed object
     */
    protected function checkFeedUrl(Feed $feed)
    {
        if ($feed->getFeedUrl() === '') {
            $feed->feedUrl = $this->fallbackUrl;
        } else {
            $feed->feedUrl = Url::resolve($feed->getFeedUrl(), $this->fallbackUrl);
        }
    }

    /**
     * Check if the site url is correct.
     *
     * @param Feed $feed Feed object
     */
    protected function checkSiteUrl(Feed $feed)
    {
        if ($feed->getSiteUrl() === '') {
            $feed->siteUrl = Url::base($feed->getFeedUrl());
        } else {
            $feed->siteUrl = Url::resolve($feed->getSiteUrl(), $this->fallbackUrl);
        }
    }

    /**
     * Check if the item url is correct.
     *
     * @param Feed $feed Feed object
     * @param Item $item Item object
     */
    protected function checkItemUrl(Feed $feed, Item $item)
    {
        $item->url = Url::resolve($item->getUrl(), $feed->getSiteUrl());
    }

    /**
     * Find the item date.
     *
     * @param SimpleXMLElement      $entry Feed item
     * @param Item                  $item  Item object
     * @param \PicoFeed\Parser\Feed $feed  Feed object
     */
    protected function findItemDate(SimpleXMLElement $entry, Item $item, Feed $feed)
    {
        $this->findItemPublishedDate($entry, $item, $feed);
        $published = $item->getPublishedDate();

        $this->findItemUpdatedDate($entry, $item, $feed);
        $updated = $item->getUpdatedDate();

        if ($published === null && $updated === null) {
            $item->setDate($feed->getDate()); // We use the feed date if there is no date for the item
        } elseif ($published !== null && $updated !== null) {
            $item->setDate(max($published, $updated)); // We use the most recent date between published and updated
        } else {
            $item->setDate($updated ?: $published);
        }
    }

    /**
     * Generate a unique id for an entry (hash all arguments).
     *
     * @return string
     */
    protected function generateId()
    {
        return hash($this->hashAlgo, implode(func_get_args()));
    }

    /**
     * Register all supported namespaces to be used within an xpath query.
     *
     * @param SimpleXMLElement $xml Feed xml
     * @return SimpleXMLElement
     */
    protected function registerSupportedNamespaces(SimpleXMLElement $xml)
    {
        foreach ($this->namespaces as $prefix => $ns) {
            $xml->registerXPathNamespace($prefix, $ns);
        }

        return $xml;
    }
}
