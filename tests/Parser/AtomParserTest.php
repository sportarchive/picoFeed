<?php

namespace PicoFeed\Parser;

use PHPUnit_Framework_TestCase;
use PicoFeed\Processor\ItemPostProcessor;

class AtomParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AtomParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = FeedParser::getParser(FeedParser::ATOM);
    }

    public function testBadInput()
    {
        $this->setExpectedException('PicoFeed\Parser\MalformedXmlException');
        $this->parser->execute('foobar');
    }

    public function testGetItemsTree()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertCount(4, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertCount(4, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertCount(4, $feed->items);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals(array(), $feed->items);
    }

    public function testFindFeedTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('литература на   русском языке,  либо написанная русскими авторами', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_fallback_on_invalid_feed_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getTitle());
    }

    public function testFindFeedDescription()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals("Зародилась во второй половине   X века, однако до XIX века,\nкогда начался её «золотой век», была практически неизвестна\nв мире.", $feed->getDescription());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getDescription());
    }

    public function testFindFeedLogo()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/images/project-logos/ruwiki.png', $feed->getLogo());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getLogo());
    }

    public function testFindFeedIcon()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/favicon/wikipedia.ico', $feed->getIcon());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/favicon/wikipedia.ico', $feed->getIcon());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://ru.wikipedia.org/static/favicon/wikipedia.ico', $feed->getIcon());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getIcon());
    }

    public function testFindFeedUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('https://feeds.wikipedia.org/category/Russian-language_literature.xml', $feed->getFeedUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml'); // relative url
        $this->assertEquals('https://feeds.wikipedia.org/category/Russian-language_literature.xml', $feed->getFeedUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://feeds.wikipedia.org/category/Russian-language_literature.xml', $feed->getFeedUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://feeds.wikipedia.org/category/Russian-language_literature.xml', $feed->getFeedUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getFeedUrl());
    }

    public function testFindSiteUrl()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml')); // rel="alternate"
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml'); // no rel + relative url
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Category:Russian-language_literature', $feed->getSiteUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getSiteUrl());
    }

    public function testFindFeedId()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('urn:uuid:bd0b2c90-35a3-44e9-a491-4e15508f6d83', $feed->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('urn:uuid:bd0b2c90-35a3-44e9-a491-4e15508f6d83', $feed->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('urn:uuid:bd0b2c90-35a3-44e9-a491-4e15508f6d83', $feed->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getId());
    }

    public function testFindFeedDate()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals(1433451900, $feed->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), 1);
    }

    public function testFindFeedLanguage()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_extra.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        // do not use lang from entry or descendant of entry
        $feed = $this->parser->execute('<feed xmlns="http://www.w3.org/2005/Atom"><entry xml:lang="ru"><title xml:lang="ru"/></entry></feed>');
        $this->assertEquals('', $feed->getLanguage());

        // do not use lang from entry or descendant of entry (prefixed)
        $feed = $this->parser->execute('<feed xmlns:atom="http://www.w3.org/2005/Atom"><atom:entry xml:lang="ru"><atom:title xml:lang="ru"/></atom:entry></feed>');
        $this->assertEquals('', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('ru', $feed->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_feed.xml'));
        $this->assertEquals('', $feed->getLanguage());
    }

    public function testFindItemId()
    {
        // items[0] === alternate generation
        // items[1] === id element
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());
        $this->assertEquals('b64b5e0ce422566fa768e8c66da61ab5759c00b2289adbe8fe2f35ecfe211184', $feed->items[1]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());
        $this->assertEquals('b64b5e0ce422566fa768e8c66da61ab5759c00b2289adbe8fe2f35ecfe211184', $feed->items[1]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('eb6f2d388a77e1f7d067a924970622d630031365fd444abe776d974d95b21990', $feed->items[0]->getId());
        $this->assertEquals('b64b5e0ce422566fa768e8c66da61ab5759c00b2289adbe8fe2f35ecfe211184', $feed->items[1]->getId());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', $feed->items[0]->getId());
    }

    public function testFindItemUrl()
    {
        // items[0] === rel="alternate"
        // items[1] === no rel
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl());
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl());

        // relative url
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_extra.xml'), 'https://feeds.wikipedia.org/category/Russian-language_literature.xml');
        $this->assertEquals('https://feeds.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl());
        $this->assertEquals('https://feeds.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl());
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/War_and_Peace', $feed->items[0]->getUrl());
        $this->assertEquals('https://en.wikipedia.org/wiki/Crime_and_Punishment', $feed->items[1]->getUrl());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getUrl());
    }

    public function testFindItemTitle()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('Война  и мир', $feed->items[0]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_fallback_on_invalid_item_values.xml'));
        $this->assertEquals('https://en.wikipedia.org/wiki/Doctor_Zhivago_(novel)', $feed->items[2]->getTitle());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getTitle());
    }

    public function testItemDate()
    {
        // items[0] === updated element
        // items[1] === published element
        // items[2] === fallback to feed date
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp());
        $this->assertEquals(1433451720, $feed->items[1]->getDate()->getTimestamp());
        $this->assertEquals(1433451900, $feed->items[2]->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp());
        $this->assertEquals(1433451720, $feed->items[1]->getDate()->getTimestamp());
        $this->assertEquals(1433451900, $feed->items[2]->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals(1433451720, $feed->items[0]->getDate()->getTimestamp());
        $this->assertEquals(1433451720, $feed->items[1]->getDate()->getTimestamp());
        $this->assertEquals(1433451900, $feed->items[2]->getDate()->getTimestamp());

        // prefer most recent date and not a particular date element
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_element_preference.xml'));
        $this->assertEquals(1433455500, $feed->items[0]->getDate()->getTimestamp());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals(time(), $feed->getDate()->getTimestamp(), '', 1);
    }

    public function testItemLanguage()
    {
        // items[0] === language tag on Language-Sensitive element (title)
        // items[1] === language tag on root node
        // items[2] === fallback to feed language
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('bg', $feed->items[0]->getLanguage());
        $this->assertEquals('bg', $feed->items[1]->getLanguage());
        $this->assertEquals('ru', $feed->items[2]->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('bg', $feed->items[0]->getLanguage());
        $this->assertEquals('bg', $feed->items[1]->getLanguage());
        $this->assertEquals('ru', $feed->items[2]->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('bg', $feed->items[0]->getLanguage());
        $this->assertEquals('bg', $feed->items[1]->getLanguage());
        $this->assertEquals('ru', $feed->items[2]->getLanguage());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getLanguage());
    }

    public function testItemAuthor()
    {
        // items[0] === item author
        // items[1] === feed author via empty fallback
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('Лев  Николаевич Толсто́й', $feed->items[0]->getAuthor());
        $this->assertEquals('Вики  педии - свободной энциклопедии', $feed->items[1]->getAuthor());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getAuthor());
    }

    public function testItemContent()
    {
        $this->parser = FeedParser::getParser(FeedParser::ATOM, new ItemPostProcessor());

        // items[0] === <summary>
        // items[1] === <content> CDATA raw html
        // items[2] === <content> escaped html
        // items[3] === <content> raw html
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);
        $this->assertTrue(strpos($feed->items[2]->getContent(), "<h1>\nДоктор Живаго\n</h1>\n<p>\n<b>«До́ктор Жива́го»</b> ") === 0);
        $this->assertTrue(strpos($feed->items[3]->getContent(), "<h1>\nГерой нашего времени\n</h1><p>\n<b>«Геро́й на́шего вре́мени»</b> \n(написан в 1838—1840) — знаменитый роман \n<a href=\"/wiki/%D0%9B") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);
        $this->assertTrue(strpos($feed->items[2]->getContent(), "<h1>\nДоктор Живаго\n</h1>\n<p>\n<b>«До́ктор Жива́го»</b> ") === 0);
        $this->assertTrue(strpos($feed->items[3]->getContent(), "<h1>\nГерой нашего времени\n</h1><p>\n<b>«Геро́й на́шего вре́мени»</b> \n(написан в 1838—1840) — знаменитый роман \n<a href=\"/wiki/%D0%9B") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertTrue(strpos($feed->items[0]->getContent(), "В наброске  предисловия к «Войне и миру» Толстой\nписал, что в 1856 г.") === 0);
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);
        $this->assertTrue(strpos($feed->items[2]->getContent(), "<h1>\nДоктор Живаго\n</h1>\n<p>\n<b>«До́ктор Жива́го»</b> ") === 0);
        $this->assertTrue(strpos($feed->items[3]->getContent(), "<h1>\nГерой нашего времени\n</h1><p>\n<b>«Геро́й на́шего вре́мени»</b> \n(написан в 1838—1840) — знаменитый роман \n<a href=\"/wiki/%D0%9B") === 0);

        // <content> is preferred over <summary>
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_element_preference.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "<h1>\nИстория  создания\n</h1>\n<p>\nОсенью \n<a href=\"/wiki/1865_%D0%B3%D0%BE%D0%B4\"") === 0);

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_fallback_on_invalid_item_values.xml'));
        $this->assertTrue(strpos($feed->items[1]->getContent(), "Осенью 1865 года, потеряв  все свои\nденьги в казино") === 0); // <content> => <summary>

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getContent());
    }

    public function testFindItemEnclosure()
    {
        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom.xml'));
        $this->assertEquals('https://upload.wikimedia.org/wikipedia/commons/4/41/War-and-peace_1873.gif', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('image/gif', $feed->items[0]->getEnclosureType());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_no_default_namespace.xml'));
        $this->assertEquals('https://upload.wikimedia.org/wikipedia/commons/4/41/War-and-peace_1873.gif', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('image/gif', $feed->items[0]->getEnclosureType());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_prefixed.xml'));
        $this->assertEquals('https://upload.wikimedia.org/wikipedia/commons/4/41/War-and-peace_1873.gif', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('image/gif', $feed->items[0]->getEnclosureType());

        $feed = $this->parser->execute(file_get_contents('tests/fixtures/atom_empty_item.xml'));
        $this->assertEquals('', $feed->items[0]->getEnclosureUrl());
        $this->assertEquals('', $feed->items[0]->getEnclosureType());
    }
}
