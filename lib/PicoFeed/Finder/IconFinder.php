<?php

namespace PicoFeed\Finder;

use DOMXPath;
use PicoFeed\Http\Url;
use PicoFeed\Parser\XmlParser;
use PicoFeed\Subscription\SubscriptionIcon;

/**
 * Class IconFinder
 *
 * @package PicoFeed\Finder
 * @author  Frederic Guillot
 */
class IconFinder
{
    /**
     * Extract the icon links from the HTML.
     *
     * @param  string $html
     * @param  string $websiteUrl
     * @return \PicoFeed\Subscription\SubscriptionIcon[]
     */
    public function findIcons($html, $websiteUrl)
    {
        $icons = array();

        if (! empty($html)) {
            $dom = XmlParser::getHtmlDocument($html);

            $xpath = new DOMXpath($dom);
            $elements = $xpath->query('//link[@rel="icon" or @rel="shortcut icon" or @rel="icon shortcut"]');

            for ($i = 0; $i < $elements->length; ++$i) {
                $icons[] = SubscriptionIcon::create()->setUrl($elements->item($i)->getAttribute('href'));
            }
        }

        $siteUrl = new Url($websiteUrl);
        $icons[] = SubscriptionIcon::create()->setUrl($siteUrl->getBaseUrl('/favicon.ico'));

        return $icons;
    }
}
