<?php

namespace PicoFeed\Finder;

use DOMXPath;
use PicoFeed\Http\Url;
use PicoFeed\Logging\Logger;
use PicoFeed\Parser\XmlParser;
use PicoFeed\Subscription\Subscription;

/**
 * Class SubscriptionFinder
 *
 * @package PicoFeed\Finder
 * @author  Frederic Guillot
 */
class SubscriptionFinder
{
    /**
     * Find list of subscriptions embedded in a HTML document
     *
     * @param  string $html
     * @param  string $websiteUrl
     * @return Subscription[]
     */
    public function findSubscriptions($html, $websiteUrl)
    {
        Logger::setMessage(get_called_class().': Search subscriptions');

        $dom = XmlParser::getHtmlDocument($html);
        $xpath = new DOMXPath($dom);
        $links = array();

        $queries = array(
            '//link[@type="application/rss+xml"]',
            '//link[@type="application/atom+xml"]',
        );

        foreach ($queries as $query) {
            $nodes = $xpath->query($query);

            foreach ($nodes as $node) {
                $link = $node->getAttribute('href');

                if (!empty($link)) {
                    $feedUrl = new Url($link);
                    $siteUrl = new Url($websiteUrl);
                    $subscriptionLink = $feedUrl->getAbsoluteUrl($feedUrl->isRelativeUrl() ? $siteUrl->getBaseUrl() : '');

                    $links[] = Subscription::create()
                        ->setFeedUrl($subscriptionLink)
                        ->setTitle($node->getAttribute('title') ?: $subscriptionLink);
                }
            }
        }

        Logger::setMessage(get_called_class().': '.implode(', ', $links));

        return $links;
    }
}
