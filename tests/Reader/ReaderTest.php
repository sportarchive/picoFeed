<?php

namespace PicoFeed\Reader;

use PHPUnit_Framework_TestCase;
use PicoFeed\Parser\FeedParser;

class ReaderTest extends PHPUnit_Framework_TestCase
{
    public function testPrependScheme()
    {
        $reader = new Reader();
        $this->assertEquals('http://http.com', $reader->prependScheme('http.com'));
        $this->assertEquals('http://boo.com', $reader->prependScheme('boo.com'));
        $this->assertEquals('http://google.com', $reader->prependScheme('http://google.com'));
        $this->assertEquals('https://google.com', $reader->prependScheme('https://google.com'));
    }

    /**
     * @group online
     */
    public function testDownloadHTTP()
    {
        $reader = new Reader();
        $feed = $reader->download('http://wordpress.org/news/feed/')->getContent();
        $this->assertNotEmpty($feed);
    }

    /**
     * @group online
     */
    public function testDownloadHTTPS()
    {
        $reader = new Reader();
        $feed = $reader->download('https://wordpress.org/news/feed/')->getContent();
        $this->assertNotEmpty($feed);
    }

    /**
     * @group online
     */
    public function testDownloadCache()
    {
        $reader = new Reader();
        $resource = $reader->download('http://linuxfr.org/robots.txt');
        $this->assertTrue($resource->isModified());

        $lastModified = $resource->getLastModified();
        $etag = $resource->getEtag();

        $reader = new Reader();
        $resource = $reader->download('http://linuxfr.org/robots.txt', $lastModified, $etag);
        $this->assertFalse($resource->isModified());
    }

    public function testDetectFormat()
    {
        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/podbean.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/jeux-linux.fr.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/sametmax.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS92, $reader->detectFormat(file_get_contents('tests/fixtures/rss_0.92.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS91, $reader->detectFormat(file_get_contents('tests/fixtures/rss_0.91.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS10, $reader->detectFormat(file_get_contents('tests/fixtures/planete-jquery.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/rss2sample.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::ATOM, $reader->detectFormat(file_get_contents('tests/fixtures/atomsample.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/cercle.psy.xml')));

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat(file_get_contents('tests/fixtures/ezrss.it')));

        $content = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" media="screen" href="/~d/styles/rss2titles.xsl"?><?xml-stylesheet type="text/css" media="screen" href="http://feeds.feedburner.com/~d/styles/itemtitles.css"?><rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" xmlns:media="http://search.yahoo.com/mrss/" xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#" xmlns:feedburner="http://rssnamespace.org/feedburner/ext/1.0" version="2.0">';

        $reader = new Reader();
        $this->assertEquals(FeedParser::RSS20, $reader->detectFormat($content));
    }

    /**
     * @group online
     */
    public function testDiscover()
    {
        $reader = new Reader();
        $client = $reader->discover('http://www.universfreebox.com/');
        $this->assertEquals('http://www.universfreebox.com/backend.php', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\Rss20Parser', $reader->getParser($client->getContent()));

        $reader = new Reader();
        $client = $reader->discover('http://cabinporn.com/');
        $this->assertEquals('http://cabinporn.com/rss', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\Rss20Parser', $reader->getParser($client->getContent()));

        $reader = new Reader();
        $client = $reader->discover('http://linuxfr.org/');
        $this->assertEquals('http://linuxfr.org/news.atom', $client->getUrl());
        $this->assertInstanceOf('PicoFeed\Parser\AtomParser', $reader->getParser($client->getUrl()));
    }

    public function testGetParserUsesHTTPEncoding()
    {
        $data = file_get_contents('tests/fixtures/cercle.psy.xml');
        $reader = new Reader();
        $parser = $reader->getParser($data);
        $feed = $parser->execute($data, 'http://blah', 'iso-8859-1');
        $this->assertInstanceOf('PicoFeed\Parser\Rss20Parser', $parser);
        $this->assertNotEmpty($feed->items);
    }

    public function testGetParserUsesSiteURL()
    {
        $data = file_get_contents('tests/fixtures/groovehq.xml');
        $reader = new Reader();
        $feed = $reader->getParser($data)->execute($data, 'http://groovehq.com/');
        $this->assertEquals('http://groovehq.com/articles.xml', $feed->getFeedUrl());
    }

    public function testFeedsReportedAsNotWorking()
    {
        $data = file_get_contents('tests/fixtures/ezrss.it');
        $reader = new Reader();
        $feed = $reader->getParser($data)->execute($data, 'http://blah');
        $this->assertNotEmpty($feed->items);
    }
}
