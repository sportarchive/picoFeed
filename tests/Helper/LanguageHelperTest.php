<?php

namespace PicoFeed\Helper;

class RtlHelperTest
{
    public function testLangRTL()
    {
        $this->assertFalse(LanguageHelper::isLanguageRTL('fr-FR'));
        $this->assertTrue(LanguageHelper::isLanguageRTL('ur'));
        $this->assertTrue(LanguageHelper::isLanguageRTL('syr-**'));
        $this->assertFalse(LanguageHelper::isLanguageRTL('ru'));
    }
}
