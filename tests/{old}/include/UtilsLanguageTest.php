<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

require_once 'include/utils.php';

/**
 * utils.php language tests
 */
class UtilsLanguageTest extends TestCase
{
    private $backup = [
        'default_language',
        'disabled_languages',
        'languages',
    ];

    protected function setUp(): void
    {
        global $sugar_config;

        foreach ($this->backup as $var) {
            if (!empty($sugar_config[$var])) {
                $this->$var = $sugar_config[$var];
            }
        }

        $sugar_config['default_language'] = 'fr_FR';
        $sugar_config['disabled_languages'] = 'es_ES,fr_FR';
        $sugar_config['languages'] = [
            'en_us' => 'English (US)',
            'bg_BG' => 'Български',
            'cs_CZ' => 'Česky',
            'da_DK' => 'Dansk',
            'de_DE' => 'Deutsch',
            'el_EL' => 'Ελληνικά',
            'es_ES' => 'Español',
            'fr_FR' => 'Français',
        ];
    }

    protected function tearDown(): void
    {
        global $sugar_config;

        foreach ($this->backup as $var) {
            unset($sugar_config[$var]);
            if (!empty($this->$var)) {
                $sugar_config[$var] = $this->$var;
            }
        }
    }

    /**
     * Make sure get_languages doesn't disable the default language
     */
    public function testGetLanguages()
    {
        global $sugar_config;
        $availableLanguages = get_languages();

        $this->assertNotEquals(
            false,
            array_key_exists($sugar_config['default_language'], $availableLanguages),
            'Default language is disabled'
        );
    }
}
