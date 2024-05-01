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

/**
 * @coversDefaultClass Localization
 */
class LocalizationTest extends TestCase
{
    /**
     * @var \Currency|mixed
     */
    //@codingStandardsIgnoreStart
    public $_currency;
    //@codingStandardsIgnoreEnd

    /**
     * @var Localization
     */
    private $locale;

    /**
     * @var User
     */
    private $user;

    /**
     * pre-class environment setup
     *
     * @access public
     */
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');

        global $app_list_strings;
        $app_list_strings['salutation_dom']['Ms.'] = 'Frau';
    }

    protected function setUp(): void
    {
        global $current_user;
        $this->locale = Localization::getObject();
        $this->user = SugarTestUserUtilities::createAnonymousUser();
        $current_user = $this->user;
        $this->_currency = SugarTestCurrencyUtilities::createCurrency('Yen', '¥', 'YEN', 78.87);
    }

    protected function tearDown(): void
    {
        // remove test user
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->locale);
        unset($this->user);
        unset($this->_currency);

        // remove test currencies
        SugarTestCurrencyUtilities::removeAllCreatedCurrencies();
    }

    /**
     * post-object environment teardown
     *
     * @access public
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    public function providerGetLocaleFormattedName()
    {
        return [
            [
                't s f l',
                'Mason',
                'Hu',
                'Mr.',
                'Saler',
                'Saler Mr. Mason Hu',
            ],
            [
                'l f',
                'Mason',
                'Hu',
                '',
                '',
                'Hu Mason',
            ],
        ];
    }

    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatInUserPreference($nameFormat, $firstName, $lastName, $salutation, $title, $expectedOutput)
    {
        $this->user->setPreference('default_locale_name_format', $nameFormat);
        $outputName = $this->locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, '', $this->user);
        $this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @dataProvider providerGetLocaleFormattedName
     */
    public function testGetLocaleFormattedNameUsingFormatSpecified($nameFormat, $firstName, $lastName, $salutation, $title, $expectedOutput)
    {
        $outputName = $this->locale->getLocaleFormattedName($firstName, $lastName, $salutation, $title, $nameFormat, $this->user);
        $this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmpty()
    {
        $this->user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = ' ';
        $outputName = $this->locale->getLocaleFormattedName('', '', '', '', '', $this->user);

        $this->assertEquals($expectedOutput, $outputName);
    }

    /**
     * @ticket 26803
     */
    public function testGetLocaleFormattedNameWhenNameIsEmptyAndReturningEmptyString()
    {
        $this->user->setPreference('default_locale_name_format', 'l f');
        $expectedOutput = '';
        $outputName = $this->locale->getLocaleFormattedName('', '', '', '', '', $this->user, true);

        $this->assertEquals($expectedOutput, $outputName);
    }

    public function testCurrenciesLoadingCorrectly()
    {
        global $sugar_config;

        $currencies = $this->locale->getCurrencies();

        $this->assertEquals($currencies['-99']['name'], $sugar_config['default_currency_name']);
        $this->assertEquals($currencies['-99']['symbol'], $sugar_config['default_currency_symbol']);
        $this->assertEquals($currencies['-99']['conversion_rate'], 1);
    }

    public function testConvertingUnicodeStringBetweenCharsets()
    {
        $string = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド';

        $convertedString = $this->locale->translateCharset($string, 'UTF-8', 'EUC-CN');
        $this->assertNotEquals($string, $convertedString);

        // test for this working by being able to convert back and the string match
        $convertedString = $this->locale->translateCharset($convertedString, 'EUC-CN', 'UTF-8');
        $this->assertEquals($string, $convertedString);
    }

    public function testConvertKS_C_56011987AsCP949()
    {
        if (!function_exists('iconv')) {
            $this->markTestSkipped('Requires iconv');
        }

        $string = file_get_contents(__DIR__ . '/Bug49619.txt');

        $convertedString = $this->locale->translateCharset($string, 'KS_C_5601-1987', 'UTF-8', true);
        $this->assertNotEquals($string, $convertedString);

        // The fromCharset could be in lowercase, need to convert to uppercase before translating
        $convertedString = $this->locale->translateCharset($string, 'ks_c_5601-1987', 'UTF-8', true);
        $this->assertNotEquals($string, $convertedString);

        // test for this working by being able to convert back and the string match
        $convertedString = $this->locale->translateCharset($convertedString, 'UTF-8', 'KS_C_5601-1987', true);
        $this->assertEquals($string, $convertedString);
    }

    /**
     * @covers ::prepareNewCharset
     *
     * @param string $charset
     * @param string $expected
     * @dataProvider prepareNewCharseProvider
     */
    public function testPrepareNewCharset(string $charset, string $expected)
    {
        $this->assertEquals(
            $this->locale->prepareNewCharset($charset),
            $expected
        );
    }

    /**
     * Provides set of data for check of prepareNewCharset.
     *
     * @return array
     */
    public static function prepareNewCharseProvider()
    {
        return [
            [
                'KS_C_5601-1987',
                'CP949',
            ],
            [
                'ks_c_5601-1987',
                'CP949',
            ],
        ];
    }

    public function testCanDetectAsciiEncoding()
    {
        $string = 'string';

        $this->assertEquals(
            $this->locale->detectCharset($string),
            'ASCII'
        );
    }

    public function testCanDetectUtf8Encoding()
    {
        $string = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモガギグゲゴザジズゼゾダヂヅデド';

        $this->assertEquals(
            $this->locale->detectCharset($string),
            'UTF-8'
        );
    }

    public function testGetPrecedentPreferenceWithUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->user->setPreference('export_delimiter', 'John is Really Cool');

        $this->assertEquals(
            $this->locale->getPrecedentPreference('export_delimiter', $this->user),
            $this->user->getPreference('export_delimiter')
        );

        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }

    public function testGetPrecedentPreferenceWithNoUserPreference()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';

        $this->assertEquals(
            $this->locale->getPrecedentPreference('export_delimiter', $this->user),
            $GLOBALS['sugar_config']['export_delimiter']
        );

        $GLOBALS['sugar_config']['export_delimiter'] = $backup;
    }

    /**
     * @ticket 33086
     */
    public function testGetPrecedentPreferenceWithUserPreferenceAndSpecifiedConfigKey()
    {
        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        $GLOBALS['sugar_config']['export_delimiter'] = 'John is Cool';
        $this->user->setPreference('export_delimiter', '');
        $GLOBALS['sugar_config']['default_random_setting_for_localization_test'] = 'John is not Cool at all';

        $this->assertEquals(
            $this->locale->getPrecedentPreference('export_delimiter', $this->user, 'default_random_setting_for_localization_test'),
            $GLOBALS['sugar_config']['default_random_setting_for_localization_test']
        );

        $backup = $GLOBALS['sugar_config']['export_delimiter'];
        unset($GLOBALS['sugar_config']['default_random_setting_for_localization_test']);
    }

    /**
     * @ticket 39171
     */
    public function testGetPrecedentPreferenceForDefaultEmailCharset()
    {
        $emailSettings = ['defaultOutboundCharset' => 'something fun'];
        $this->user->setPreference('emailSettings', $emailSettings, 0, 'Emails');

        $this->assertEquals(
            $this->locale->getPrecedentPreference('default_email_charset', $this->user),
            $emailSettings['defaultOutboundCharset']
        );
    }

    /**
     * @ticket 23992
     */
    public function testGetCurrencySymbol()
    {
        $this->user->setPreference('currency', $this->_currency->id);

        $this->assertEquals(
            $this->locale->getCurrencySymbol($this->user),
            '¥'
        );
    }

    /**
     * @ticket 23992
     */
    public function testGetLocaleFormattedNumberWithNoCurrencySymbolSpecified()
    {
        $this->user->setPreference('currency', $this->_currency->id);
        $this->user->setPreference('dec_sep', '.');
        $this->user->setPreference('num_grp_sep', ',');
        $this->user->setPreference('default_currency_significant_digits', 2);

        $this->assertEquals(
            $this->locale->getLocaleFormattedNumber(20, '', true, $this->user),
            '¥20'
        );
    }

    /**
     * @bug 60672
     */
    public function testGetNumberGroupingSeparatorIfSepIsEmpty()
    {
        $this->user->setPreference('num_grp_sep', '');
        $this->assertEmpty($this->locale->getNumberGroupingSeparator(), "1000s separator should be ''");
    }

    /**
     * @param string|null $macro
     * @param SugarBean|string $bean
     * @param array|null $data
     * @param string $expected
     *
     * @dataProvider formatNameProvider
     */
    public function testFormatName($macro, $bean, $data, $expected)
    {
        if ($macro) {
            $locale = $this->getMockBuilder('Localization')
                ->setMethods(['getLocaleFormatMacro'])
                ->disableOriginalConstructor()
                ->getMock();
            $locale->expects($this->any())
                ->method('getLocaleFormatMacro')
                ->will($this->returnValue($macro));
        } else {
            $locale = $this->locale;
        }

        $actual = $locale->formatName($bean, $data);
        $this->assertEquals($expected, $actual);
    }

    public static function formatNameProvider()
    {
        $user1 = new User();
        $user1->first_name = 'John';
        $user1->last_name = 'Doe';
        $user1->user_name = 'jdoe';
        $user1->position = 'Engineer';
        $user1->name_format_map = array_merge(
            $user1->name_format_map,
            [
                'p' => 'position',
                'u' => 'user_name',
                'z' => 'non_existing_field',
            ]
        );

        $contact1 = new Contact();
        $contact1->salutation = 'Ms.';
        $contact1->first_name = 'Barbara';
        $contact1->last_name = 'Schulz';

        $contact2 = new Contact();
        $contact2->salutation = 'Sir';
        $contact2->first_name = 'Aaron';
        $contact2->last_name = 'Brown';

        $contact3 = new Contact();
        $contact3->first_name = 'David';

        $contact4 = new Contact();
        $contact4->last_name = 'Livingstone';

        return [
            'invalid-bean-type' => [null, null, null, false],
            'invalid-module' => [null, 'Apples', null, false],
            'bean-as-object' => [null, $user1, null, 'John Doe'],
            'bean-as-string' => [
                null,
                'Users',
                [
                    'first_name' => 'Judy',
                    'last_name' => 'Smith',
                ],
                'Judy Smith',
            ],
            'non-existing-token' => ['x f', $user1, null, 'John'],
            'non-existing-field' => ['z l', $user1, null, 'Doe'],
            'empty-result' => ['x z', $user1, null, ''],
            'custom-token' => ['f (u) l', $user1, null, 'John (jdoe) Doe'],
            'custom-field' => ['l, f (p)', $user1, null, 'Doe, John (Engineer)'],
            'enum-is-localized' => [null, $contact1, null, 'Frau Barbara Schulz'],
            'enum-not-found' => [null, $contact2, null, 'Sir Aaron Brown'],
            'trim-left' => ['l, f', $contact3, null, 'David'],
            'trim-right' => ['l, f', $contact4, null, 'Livingstone'],
        ];
    }

    /**
     * Test to make sure that when num_grp_sep is passed with out a sugarDefaultConfig Name it returns null if not set
     *
     * @covers ::getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsNullForNumGrpSep()
    {
        $this->assertNull($this->locale->getPrecedentPreference('num_grp_sep', $this->user));
    }

    /**
     * Test to make sure that the proper value is returned from getPrecedentPreference for num_grp_sep
     * when the user has one
     *
     * @covers ::getPrecedentPreference
     */
    public function testGetPrecedentPreferenceReturnsValueForNumGrpSep()
    {
        $this->user->setPreference('num_grp_sep', '!');
        $this->assertEquals('!', $this->locale->getPrecedentPreference('num_grp_sep', $this->user));
    }

    /**
     * Test to retrieve authenticated user's preferred language
     */
    public function testGetAuthenticatedUserLanguage()
    {
        //test from user pref
        $this->user->preferred_language = 'fr_FR';
        $this->assertEquals('fr_FR', $this->locale->getAuthenticatedUserLanguage());
        $this->user->preferred_language = 'de_DE';
        $this->assertEquals('de_DE', $this->locale->getAuthenticatedUserLanguage());
        //test from session
        if (!empty($_SESSION['authenticated_user_language'])) {
            $oSESSION = $_SESSION['authenticated_user_language'];
        }
        $this->user->preferred_language = null;
        $_SESSION['authenticated_user_language'] = 'ja_JP';
        $this->assertEquals('ja_JP', $this->locale->getAuthenticatedUserLanguage());
        //test from default
        unset($_SESSION['authenticated_user_language']);
        $this->assertEquals($GLOBALS['sugar_config']['default_language'], $this->locale->getAuthenticatedUserLanguage());
        if (isset($oSESSION)) {
            $_SESSION['authenticated_user_language'] = $oSESSION;
        }
    }

    /**
     * Provider for testGetLocaleUnFormattedName.
     *
     * @return array
     */
    public function getLocaleUnFormattedNameProvider()
    {
        return [
            [
                'name' => 'TestMan Tester',
                'format' => 'f l',
                'expected' => [
                    'f' => 'TestMan',
                    'l' => 'Tester',
                ],
            ],
            [
                'name' => 'Mr. TestMan Tester',
                'format' => 's f l',
                'expected' => [
                    's' => 'Mr.',
                    'f' => 'TestMan',
                    'l' => 'Tester',
                ],
            ],
            [
                'name' => 'Tester, Mr. TestMan Jr.',
                'format' => 'l, s f p',
                'expected' => [
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan',
                    'p' => 'Jr.',
                ],
            ],
            [
                'name' => 'Tester, Mr. TestMan Jr.',
                'format' => 'l, s f, p',
                'expected' => [
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan Jr.',
                    'p' => '',
                ],
            ],
            [
                'name' => 'Tester, Mr. TestMan Jr., III',
                'format' => 'l, s f, p',
                'expected' => [
                    'l' => 'Tester',
                    's' => 'Mr.',
                    'f' => 'TestMan Jr.',
                    'p' => 'III',
                ],
            ],
            [
                'name' => 'TestMan Mr.TestMan Mr. Mr.Tester Tester',
                'format' => 'f s l',
                'expected' => [
                    'f' => 'TestMan Mr.TestMan',
                    's' => 'Mr.',
                    'l' => 'Mr.Tester Tester',
                ],
            ],
            [
                'name' => 'TestMan TestMan Tester Tester',
                'format' => 'f s l',
                'expected' => [
                    'f' => 'TestMan',
                    's' => '',
                    'l' => 'TestMan Tester Tester',
                ],
            ],
            [
                'name' => 'TestMan Mr. TestMan, Tester Mr. Tester',
                'format' => 'f, s l',
                'expected' => [
                    'f' => 'TestMan Mr. TestMan',
                    's' => 'Mr.',
                    'l' => 'Tester',
                ],
            ],
            [
                'name' => 'TestMan Mr. TestMan, Tester Mr., Tester',
                'format' => 'f, s, l',
                'expected' => [
                    'f' => 'TestMan Mr. TestMan',
                    's' => 'Mr.',
                    'l' => 'Tester',
                ],
            ],
            [
                'name' => 'Mr. TestMan Tester',
                'format' => 's f, l',
                'expected' => [
                    's' => 'Mr.',
                    'f' => 'TestMan Tester',
                    'l' => '',
                ],
            ],
            [
                'name' => 'Robert John Downey Jr.',
                'format' => 'f t l p',
                'expected' => [
                    'f' => 'Robert',
                    't' => 'John',
                    'l' => 'Downey',
                    'p' => 'Jr.',
                ],
            ],
            [
                'name' => 'Robert John Downey Jr.',
                'format' => 'f s l',
                'expected' => [
                    'f' => 'Robert',
                    's' => '',
                    'l' => 'John Downey Jr.',
                ],
            ],
            [
                'name' => 'Robert John Downey Jr.',
                'format' => 's l',
                'expected' => [
                    's' => '',
                    'l' => 'Robert John Downey Jr.',
                ],
            ],
        ];
    }

    /**
     * Test parse fullname
     *
     * @param $name
     * @param $format
     * @param $expected
     *
     * @covers ::getLocaleUnFormattedName
     * @dataProvider getLocaleUnFormattedNameProvider
     */
    public function testGetLocaleUnFormattedName($name, $format, $expected)
    {
        $result = $this->locale->getLocaleUnFormattedName($name, $format);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provider for testValidateMbEncoding
     * @return array
     */
    public function validateMbEncodingProvider()
    {
        return [
            // Test an encoding that is known to be in mb_list_encodings
            ['EUC-CN', true],
            // Test that capitalization doesn't matter
            ['euC-cN', true],
            // Test an alias of that encoding that is known to not be in mb_list_encodings
            ['gb2312', true],
            // Test a fake encoding
            ['not a real encoding', false],
        ];
    }

    /**
     * @covers ::validateMbEncoding
     * @covers ::getValidMbEncodings
     * @dataProvider validateMbEncodingProvider
     * @param string $encoding the encoding to test
     * @param bool $expected the expected result
     */
    public function testValidateMbEncoding($encoding, $expected)
    {
        $result = $this->locale->validateMbEncoding($encoding);
        $this->assertEquals($expected, $result);
    }
}
