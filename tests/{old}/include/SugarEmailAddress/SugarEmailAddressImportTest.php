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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @coversDefaultClass Importer
 */
class SugarEmailAddressImportTest extends TestCase
{
    public const PRIMARY_EMAIL = 'primary@foo.bar';
    public const NON_PRIMARY_EMAIL_OPTED_IN = 'non_primary_opted_in@foo.bar';
    public const NON_PRIMARY_EMAIL_OPTED_OUT = 'non_primary_opted_out@foo.bar';

    private $importData = [];
    private $configOptoutBackUp;
    private $fileName = 'upload://import_email_properties.csv';

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp(): void
    {
        if (isset($GLOBALS['sugar_config']['new_email_addresses_opted_out'])) {
            $this->configOptoutBackUp = $GLOBALS['sugar_config']['new_email_addresses_opted_out'];
        }

        unlink($this->fileName);

        $id = Uuid::uuid1();
        $this->importData = [
            'id' => $id,
            'first_name' => 'ContactFirstName',
            'last_name' => 'ContactLastName',
            'email' => static::PRIMARY_EMAIL,
            'email_addresses_non_primary' => static::NON_PRIMARY_EMAIL_OPTED_IN . ',0,0;' .
                static::NON_PRIMARY_EMAIL_OPTED_OUT . ',0,1',
        ];
        SugarTestContactUtilities::setCreatedContact([$id]);
    }

    protected function tearDown(): void
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        $this->importData = [];
        unlink($this->fileName);

        if (isset($this->configOptoutBackUp)) {
            $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $this->configOptoutBackUp;
        } else {
            unset($GLOBALS['sugar_config']['new_email_addresses_opted_out']);
        }
    }

    public function optoutDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @covers ::import
     * @dataProvider optoutDataProvider
     */
    public function testImportEmailAddress_OptoutMapped_ImportsCSVFileValuesCorrectly(bool $optOut)
    {
        $importValue = intval($optOut);
        $this->importData['email_opt_out'] = "{$importValue}";
        $this->createImportFile($this->importData);
        $bean = BeanFactory::newBean('Contacts');
        $importSource = new ImportFile($this->fileName, ',', '"');
        $importer = new Importer($importSource, $bean);
        $importer->import();

        $contact = BeanFactory::retrieveBean('Contacts', $this->importData['id']);
        $this->assertNotEmpty($contact, 'Contact record was not created on Import');

        $this->assertSame($contact->first_name, $this->importData['first_name'], 'Contact first_name not imported');
        $this->assertSame($contact->last_name, $this->importData['last_name'], 'Contact last_name not imported');
        $emailProperties = $this->getEmailProperties($contact);

        $this->assertSame(
            3,
            safeCount($emailProperties),
            'Expected 3 Emails To Be Created'
        );

        $this->assertSame(
            boolval($this->importData['email_opt_out']),
            boolval($emailProperties[static::PRIMARY_EMAIL]['opt_out']),
            'Opt_out value on created Email Address Value does not match Opt-Out Value Supplied in Import Record'
        );

        $this->assertSame(
            false,
            boolval($emailProperties[static::NON_PRIMARY_EMAIL_OPTED_IN]['opt_out']),
            'Opt_out value on created Non-Primary Email Address Value should be 0 (Opted-In)'
        );

        $this->assertSame(
            true,
            boolval($emailProperties[static::NON_PRIMARY_EMAIL_OPTED_OUT]['opt_out']),
            'Opt_out value on created Non-Primary Email Address Value should be 1 (Opted-Out)'
        );
    }

    /**
     * @covers ::import
     * @dataProvider optoutDataProvider
     */
    public function testImportEmailAddress_OptoutNotMapped_DefaultsToConfigValue(bool $defaultOptout)
    {
        $GLOBALS['sugar_config']['new_email_addresses_opted_out'] = $defaultOptout;

        $this->createImportFile($this->importData);
        $bean = BeanFactory::newBean('Contacts');
        $importSource = new ImportFile($this->fileName, ',', '"');
        $importer = new Importer($importSource, $bean);
        $importer->import();

        $contact = BeanFactory::retrieveBean('Contacts', $this->importData['id']);
        $this->assertNotEmpty($contact, 'Contact record was not created on Import');

        $this->assertSame($contact->first_name, $this->importData['first_name'], 'Contact first_name not imported');
        $this->assertSame($contact->last_name, $this->importData['last_name'], 'Contact last_name not imported');
        $emailProperties = $this->getEmailProperties($contact);

        $this->assertSame(
            3,
            safeCount($emailProperties),
            'Expected 3 Emails To Be Created'
        );

        $this->assertSame(
            $defaultOptout,
            boolval($emailProperties[static::PRIMARY_EMAIL]['opt_out']),
            'Opt_out value on created Email Address Value does not match Opt-Out Value Supplied in Import Record'
        );

        $this->assertSame(
            $defaultOptout,
            boolval($emailProperties[static::NON_PRIMARY_EMAIL_OPTED_IN]['opt_out']),
            'Opt_out value on created Non-Primary Email Address Value should be 0 (Opted-In)'
        );

        $this->assertSame(
            $defaultOptout,
            boolval($emailProperties[static::NON_PRIMARY_EMAIL_OPTED_OUT]['opt_out']),
            'Opt_out value on created Non-Primary Email Address Value should be 1 (Opted-Out)'
        );
    }

    private function getEmailProperties($contact)
    {
        $emailProperties = [];
        foreach ($contact->email as $emailData) {
            $emailAddress = $emailData['email_address'];
            $emailProperties[$emailAddress] = $emailData;
        }
        return $emailProperties;
    }

    private function createImportFile($importData)
    {
        $fields = array_keys($importData);
        $fieldCount = count($fields);

        $_REQUEST['columncount'] = $fieldCount;
        $_REQUEST['import_module'] = 'Contacts';

        for ($i = 0; $i < $fieldCount; $i++) {
            $_REQUEST["colnum_{$i}"] = $fields[$i];
        }

        $values = array_values($importData);
        $data = '"' . implode('","', $values) . "\"\n";
        file_put_contents($this->fileName, $data);
    }
}
