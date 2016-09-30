<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;
use PicoFeed\Processor\ItemPostProcessor;

class Rss20ParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Rss20Parser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = FeedParser::getParser(FeedParser::RSS20);
    }

    public function testBadInput()
    {
        $this->setExpectedException('PicoFeed\Parser\MalformedXmlException');
        $this->parser->execute('foobar');
    }

    public function testGetItemsTree()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertCount(4, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals(array(), $feed->items);
    }

    public function testFindFeedTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_fallback_on_invalid_feed_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals('', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindFeedDescription()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals('', $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals('', $feed->getDescription());
    }

    public function testFindFeedLogo()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals('', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals('', $feed->getLogo());
    }

    public function testFindFeedIcon()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('', $feed->getIcon());
    }

    public function testFindFeedUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('', $feed->getFeedUrl());
    }

    public function testFindSiteUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml'); // relative url
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals('', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals('', $feed->getSiteUrl());
    }

    public function testFindFeedId()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getId());
    }

    public function testFindFeedDate()
    {
        // pubDate
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        // lastBuildDate
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_extra.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        // prefer most recent date and not a particular date element
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_element_preference.xml'));
        $this->assertEquals(1433455500, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
    }

    public function testFindFeedLanguage()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_channel.xml'));
        $this->assertEquals('', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_feed.xml'));
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindItemId()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml')); // <guid>
        $this->assertEquals('06e53052cd17cdfb264d9c37d495cc3746ac43f79488c7ce67894e718f674bd5', $feed->items[1]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml')); // alternate generation
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', $feed->items[0]->getId());
    }

    public function testFindItemUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <atom:link>
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getUrl()); // <feedburner:origLink>
        $this->assertEquals('https://guid.wikipedia.org/wiki/A_Hero_of_Our_Time', $feed->items[3]->getUrl()); // <guid>

        // relative urls
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $this->assertEquals('https://feeds.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <rss:link>
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <atom:link>
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getUrl()); // <feedburner:origLink>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_element_preference.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl()); // <feedburner:origLink> is preferred over <rss:link>, <atom:link>, <guid>
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl()); // <rss:link> is preferred over <atom:link>, <guid>
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getUrl()); // <atom:link> is preferred over <guid>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_fallback_on_invalid_item_values.xml'));
        $this->assertEquals('', $feed->items[0]->getUrl()); // <guid> is invalid URI

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getUrl());
    }

    public function testFindItemTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_fallback_on_invalid_item_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getTitle());
    }

    public function testFindItemDate()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp()); // item date
        $this->assertEquals(1433451900, $feed->items[1]->getDate()->getTimestamp()); // fallback to feed date

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals(time(), $feed->items[0]->getDate()->getTimestamp(), 1);
    }

    public function testFindItemLanguage()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('bg', $feed->items[0]->getLanguage()); // item language
        $this->assertEquals('ru', $feed->items[1]->getLanguage()); // fallback to feed language

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getLanguage());
    }

    public function testFindItemAuthor()
    {
        // items[0] === item author
        // items[1] === feed author via empty fallback (channel/managingEditor)
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_dc.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        // <dc:creator> is preferred over <author>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_element_preference.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testFindItemContent()
    {
        $this->parser = FeedParser::getParser(FeedParser::RSS20, new ItemPostProcessor());

        // items[0] === <description>
        // items[1] === <content:encoded>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        // <content:encoding> is preferred over <description>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_element_preference.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_fallback_on_invalid_item_values.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "Осенью 1865 года, потеряв  все свои\nденьги в казино") === 0); // <content:encoded> => <description>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getContent());
    }

    public function testFindItemEnclosure()
    {
        // Test tests covers the preference of <feedburner:origEnclosureLink> over <enclosure> as well
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20.xml'));
        $this->assertEquals('https://upload.wikimedia.org/wikipedia/commons/4/41/War-and-peace_1873.gif', $feed->items[0]->getEnclosureUrl()); // <enclosure>
        $this->assertEquals('image/gif', $feed->items[0]->getEnclosureType());
        $this->assertEquals('https://upload.wikimedia.org/wikipedia/commons/7/7b/Crime_and_Punishment-1.png', $feed->items[1]->getEnclosureUrl()); // <feedburner:origEnclosureLink>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/rss_20_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('', $feed->items[0]->getEnclosureType());
    }
}
