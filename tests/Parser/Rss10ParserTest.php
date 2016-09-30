<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;
use PicoFeed\Processor\ItemPostProcessor;

class Rss10ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rss10Parser
     */
    private $parser;
    
    public function setUp()
    {
        $this->parser = FeedParser::getParser(FeedParser::RSS10);
    }
    
    public function testBadInput()
    {
        $this->setExpectedException('PicoFeed\Parser\MalformedXmlException');
        $this->parser->execute('foobar');
    }

    public function testGetItemsTree()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertCount(2, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertCount(3, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertCount(1, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertSame(array(), $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertCount(60, $feed->items);
    }

    public function testFindFeedTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_feed_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals('', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals('', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('heise online News', $feed->getTitle());
    }

    public function testFindFeedDescription()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals('', $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals('', $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('Nachrichten nicht nur aus der Welt der Computer', $feed->getDescription());
    }

    public function testFindFeedLogo()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals('', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals('', $feed->getLogo());
    }

    public function testFindFeedIcon()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('', $feed->getIcon());
    }

    public function testFindFeedUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('', $feed->getFeedUrl());
    }

    public function testFindSiteUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals('', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals('', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('http://www.heise.de/newsticker/', $feed->getSiteUrl());
    }

    public function testFindFeedId()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('http://www.heise.de/newsticker/', $feed->getId());
    }

    public function testFindFeedDate()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
    }

    public function testFindFeedLanguage()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_channel.xml'));
        $this->assertEquals('', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_feed.xml'));
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindItemId()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', $feed->items[0]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('86f8961705a56fed5f46ff1a013a2429f4de3d617048b8151e7f6fa0a2abb985', $feed->items[0]->getId());
    }

    public function testFindItemUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <feedburner:origLink>

        // relative urls
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $this->assertEquals('https://feeds.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <feedburner:origLink>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_element_preference.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <feedburner:origLink> is preferred over <rss:link>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('http://www.heise.de/newsticker/meldung/Facebook-f8-Kuenstliche-Intelligenz-raeumt-den-Newsfeed-auf-3173304.html?wt_mc=rss.ho.beitrag.rdf', $feed->items[0]->getUrl());
    }

    public function testFindItemTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_item_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('Facebook f8: Künstliche Intelligenz räumt den Newsfeed auf', $feed->items[0]->getTitle());
    }

    public function testFindItemDate()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp()); // item date
        $this->assertEquals(1433451900, $feed->items[1]->getDate()->getTimestamp()); // fallback to feed date

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals(time(), $feed->items[0]->getDate()->getTimestamp(), 1);
    }

    public function testFindItemLanguage()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('bg', $feed->items[0]->getLanguage()); // item language
        $this->assertEquals('ru', $feed->items[1]->getLanguage()); // fallback to feed language

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testFindItemAuthor()
    {
        // items[0] === item author
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testFindItemContent()
    {
        $this->parser = FeedParser::getParser(FeedParser::RSS10, new ItemPostProcessor());

        // items[0] === <description>
        // items[1] === <content:encoded>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_no_default_namespace.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_prefixed.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);

        // <content:encoding> is preferred over <description>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_element_preference.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_fallback_on_invalid_item_values.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "Осенью 1865 года, потеряв  все свои\nденьги в казино") === 0); // <content:encoded> => <description>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getContent());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/heise.rdf'));
        $this->assertEquals('Die Keynotes des zweiten Tags von Facebooks Entwicklerkonferenz haben gezeigt, an wievielen Stellen das Unternehmen künstliche Intelligenz bereits einsetzt oder damit experimentiert.', $feed->items[0]->getContent());
    }

    public function testFindItemEnclosure()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_10.xml'));
        $this->assertEquals('', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('', $feed->items[0]->getEnclosureType());
    }
}
