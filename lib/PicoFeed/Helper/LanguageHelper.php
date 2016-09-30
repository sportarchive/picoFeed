<?php

namespace PicoFeed\Helper;

/**
 * Class LanguageHelper
 *
 * @package PicoFeed\Helper
 * @author  Frederic Guillot
 */
class LanguageHelper
{
    /**
     * Return true if the given language is "Right to Left".
     *
     * @static
     * @param string $language Language: fr-FR, en-US
     * @return bool
     */
    public static function isLanguageRTL($language)
    {
        $language = strtolower($language);
        $prefixes = array(
            'ar', // Arabic (ar-**)
            'fa', // Farsi (fa-**)
            'ur', // Urdu (ur-**)
            'ps', // Pashtu (ps-**)
            'syr', // Syriac (syr-**)
            'dv', // Divehi (dv-**)
            'he', // Hebrew (he-**)
            'yi', // Yiddish (yi-**)
        );

        foreach ($prefixes as $prefix) {
            if (strpos($language, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
