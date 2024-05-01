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

class vCardTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        global $app_strings, $app_list_strings, $current_language;
        $app_strings = return_application_language($current_language);
        $app_list_strings = return_app_list_strings_language($current_language);
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    // data providers

    public function vCardsWithSalutations()
    {
        return [
            ['MAR-1510a.vcf'],
            ['MAR-1510b.vcf'],
        ];
    }

    // test cases

    /**
     * Check if exception is thrown when required fields are not present
     *
     * @ticket 60613
     * @dataProvider vCardsWithoutRequiredFields
     */
    public function testImportVCardWithoutRequiredFields($filename, $module)
    {
        $filename = __DIR__ . "/vcf/$filename";

        $vcard = new vCard();

        $this->expectException(SugarException::class);
        $vcard->importVCard($filename, $module);
    }

    public static function vCardsWithoutRequiredFields()
    {
        return [
            [
                'VCardWithoutAllRequired.vcf', // vCard without last_name
                'Contacts',
            ],
            [
                'VCardEmpty.vcf', // Empty vCard
                'Leads',
            ],
        ];
    }

    /**
     * @ticket 10419
     */
    public function testImportedVcardWithDifferentCharsetIsTranslatedToTheDefaultCharset()
    {
        $filename = __DIR__ . '/vcf/ISO88591SampleFile.vcf';

        $personMock = $this->getMockBuilder('Person')
            ->setMethods(['save'])
            ->getMock();

        $vcard = $this->getMockBuilder('vCard')
            ->setMethods(['getBean'])
            ->getMock();
        $vcard->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($personMock));
        $record = $vcard->importVCard($filename, 'PersonMock');

        $this->assertEquals('Hans Müster', $personMock->first_name . ' ' . $personMock->last_name);
    }

    public function testImportedVcardWithSameCharsetIsNotTranslated()
    {
        $filename = __DIR__ . '/vcf/UTF8SampleFile.vcf';

        $personMock = $this->getMockBuilder('Person')
            ->setMethods(['save'])
            ->getMock();
        $vcard = $this->getMockBuilder('vCard')
            ->setMethods(['getBean'])
            ->getMock();
        $vcard->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($personMock));
        $record = $vcard->importVCard($filename, 'PersonMock');

        $this->assertEquals('Hans Müster', $personMock->first_name . ' ' . $personMock->last_name);
    }

    /**
     * @dataProvider vCardsWithSalutations
     */
    public function testImportVcard_NameIncludesSalutation_PersonIsCreatedWithFirstNameAndLastNameAndSalutation($vcard)
    {
        $filename = __DIR__ . "/vcf/{$vcard}";
        $personMock = $this->getMockBuilder('Person')
            ->setMethods(['save'])
            ->getMock();
        $vcard = $this->getMockBuilder('vCard')
            ->setMethods(['getBean'])
            ->getMock();
        $vcard->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($personMock));
        $record = $vcard->importVCard($filename, 'PersonMock');
        $this->assertNotEmpty($personMock->first_name, 'The first name should have been parsed from the vcard');
        $this->assertNotEmpty($personMock->last_name, 'The last name should have been parsed from the vcard');
        $this->assertNotEmpty($personMock->salutation, 'The salutation should have been parsed from the vcard');
    }

    public function vCardNames()
    {
        return [
            ['', 'Last Name'],
            ['First Name', 'Last Name'],
            ['Иван', 'Č, Ć ŐŐŐ Lastname'],
        ];
    }

    /**
     * @ticket 24487
     * @dataProvider vCardNames
     */
    public function testExportVcard($fname, $lname)
    {
        $vcard = $this->getMockBuilder('vCard')
            ->setMethods(['getBean'])
            ->getMock();

        $data = $this->createMock('Person');
        $data->first_name = $fname;
        $data->last_name = $lname;
        $GLOBALS['current_user']->setPreference('default_export_charset', 'UTF-8');
        $vcard->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($data));

        $vcard->loadContact('person-id', 'Person');
        $cardtext = $vcard->toString();

        $this->assertStringContainsStringIgnoringCase("N;CHARSET=utf-8:$lname;$fname", $cardtext);
        $this->assertStringContainsStringIgnoringCase("FN;CHARSET=utf-8: $fname $lname", $cardtext);
    }

    public function testClear()
    {
        $vcard = new vCard();
        $vcard->setProperty('dog', 'cat');
        $vcard->clear();

        $this->assertNull($vcard->getProperty('dog'));
    }

    public function testSetProperty()
    {
        $vcard = new vCard();
        $vcard->setProperty('dog', 'cat');

        $this->assertEquals('cat', $vcard->getProperty('dog'));
    }

    public function testGetPropertyThatDoesNotExist()
    {
        $vcard = new vCard();

        $this->assertNull($vcard->getProperty('dog'));
    }

    public function testSetTitle()
    {
        $vcard = new vCard();
        $vcard->setTitle('cat');

        $this->assertEquals('cat', $vcard->getProperty('TITLE'));
    }

    public function testSetORG()
    {
        $vcard = new vCard();
        $vcard->setORG('foo', 'bar');

        $this->assertEquals('foo;bar', $vcard->getProperty('ORG'));
    }
}
