<?php

namespace PicoFeed\Finder;

use PHPUnit_Framework_TestCase;

class SubscriptionFinderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var SubscriptionFinder
     */
    private $feedFinder;
    
    public function setUp()
    {
        $this->feedFinder = new SubscriptionFinder();
    }
    
    public function testFindRssFeed()
    {
        $html = '<!DOCTYPE html><html><head>
                <link type="application/rss+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');
        $this->assertCount(1, $feeds);
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getFeedUrl());
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getTitle());
    }

    public function testFindAtomFeed()
    {
        $html = '<!DOCTYPE html><html><head>
                <link type="application/atom+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');

        $this->assertCount(1, $feeds);
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getFeedUrl());
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getTitle());
    }

    public function testFindFeedNotInHead()
    {
        $html = '<!DOCTYPE html><html><head></head>
                <body>
                <link type="application/atom+xml" href="http://miniflux.net/feed">
                <p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');

        $this->assertCount(1, $feeds);
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getFeedUrl());
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getTitle());
    }

    public function testFindNoFeedPresent()
    {
        $html = '<!DOCTYPE html><html><head>
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');
        $this->assertCount(0, $feeds);
    }

    public function testFindIgnoreUnknownType()
    {
        $html = '<!DOCTYPE html><html><head>
                <link type="application/flux+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');
        $this->assertCount(0, $feeds);
    }

    public function testFindIgnoreTypeInOtherAttribute()
    {
        $html = '<!DOCTYPE html><html><head>
                <link rel="application/rss+xml" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');
        $this->assertCount(0, $feeds);
    }

    public function testFindWithOtherAttributesPresent()
    {
        $html = '<!DOCTYPE html><html><head>
                <link rel="alternate" type="application/rss+xml" title="RSS" href="http://miniflux.net/feed">
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');

        $this->assertCount(1, $feeds);
        $this->assertEquals('http://miniflux.net/feed', $feeds[0]->getFeedUrl());
        $this->assertEquals('RSS', $feeds[0]->getTitle());
    }

    public function testFindMultipleFeeds()
    {
        $html = '<!DOCTYPE html><html><head>
                <link rel="alternate" type="application/rss+xml" title="CNN International: Top Stories" href="http://rss.cnn.com/rss/edition.rss"/>
                <link rel="alternate" type="application/rss+xml" title="Connect The World" href="http://rss.cnn.com/rss/edition_connecttheworld.rss"/>
                <link rel="alternate" type="application/rss+xml" title="World Sport" href="http://rss.cnn.com/rss/edition_worldsportblog.rss"/>
                </head><body><p>boo</p></body></html>';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://www.cnn.com/services/rss/');
        $this->assertCount(3, $feeds);

        $this->assertEquals('http://rss.cnn.com/rss/edition.rss', $feeds[0]->getFeedUrl());
        $this->assertEquals('CNN International: Top Stories', $feeds[0]->getTitle());

        $this->assertEquals('http://rss.cnn.com/rss/edition_connecttheworld.rss', $feeds[1]->getFeedUrl());
        $this->assertEquals('Connect The World', $feeds[1]->getTitle());

        $this->assertEquals('http://rss.cnn.com/rss/edition_worldsportblog.rss', $feeds[2]->getFeedUrl());
        $this->assertEquals('World Sport', $feeds[2]->getTitle());
    }

    public function testFindWithInvalidHTML()
    {
        $html = '!DOCTYPE html html head
                link type="application/rss+xml" href="http://miniflux.net/feed"
                /head body /p boo /p body /html';

        $feeds = $this->feedFinder->findSubscriptions($html, 'http://miniflux.net/');
        $this->assertCount(0, $feeds);
    }

    public function testFindWithHtmlParamEmptyString()
    {
        $feeds = $this->feedFinder->findSubscriptions('', 'http://miniflux.net/');
        $this->assertCount(0, $feeds);
    }
}