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

class FiltersTest extends TestCase
{
    /**
     * Tests may create some files to test customizations. During `setUp` we
     * need to back up these files and delete them temporarily.
     *
     * @var array The list of generated files.
     */
    public static $generatedOperatorsFile = [
        'custom/clients/base/filters/operators/operators.php',
        'clients/latrop/filters/operators/operators.php',
        'custom/clients/latrop/filters/operators/operators.php',
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('files');

        foreach (self::$generatedOperatorsFile as $file) {
            // Back up and remove existing files.
            SugarTestHelper::saveFile($file);
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected function tearDown(): void
    {
        foreach (self::$generatedOperatorsFile as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function testGetOperators()
    {
        $filters = BeanFactory::getBeanClass('Filters');
        $operators = $filters::getOperators();

        $this->assertArrayHasKey('enum', $operators, 'It should contain "enum" field type');
        $this->assertArrayHasKey('name', $operators, 'It should contain "name" field type');
        $this->assertArrayHasKey('varchar', $operators, 'It should contain "varchar" field type');
    }

    public function testGetOperatorsWithCustom()
    {
        $generatedFile = self::$generatedOperatorsFile[0];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['base']['filter']['operators'] = array(
    'enum' => array(
        '\$contains' => 'LBL_OPERATOR_CONTAINS',
        '\$not_contains' => 'LBL_OPERATOR_NOT_CONTAINS',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanClass('Filters');
        $operators = $filters::getOperators();

        $this->assertArrayHasKey('enum', $operators, 'It should contain "enum" field type');
        $this->assertArrayNotHasKey('name', $operators, 'It should not contain "name" field type');
        $this->assertArrayNotHasKey('varchar', $operators, 'It should not contain "varchar" field type');
    }

    public function testGetOperatorsForClient()
    {
        $generatedFile = self::$generatedOperatorsFile[1];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['latrop']['filter']['operators'] = array(
    'name' => array(
        '\$equals' => 'LBL_OPERATOR_MATCHES',
        '\$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanClass('Filters');
        $operators = $filters::getOperators('latrop');

        $this->assertArrayNotHasKey('enum', $operators, 'It should not contain "enum" field type');
        $this->assertArrayHasKey('name', $operators, 'It should contain "name" field type');
        $this->assertArrayNotHasKey('varchar', $operators, 'It should not contain "varchar" field type');
    }

    public function testGetOperatorsForClientWithCustom()
    {
        $generatedFile = self::$generatedOperatorsFile[2];
        $fakeMeta = <<<EOQ
<?php
\$viewdefs['latrop']['filter']['operators'] = array(
    'varchar' => array(
        '\$equals' => 'LBL_OPERATOR_MATCHES',
        '\$starts' => 'LBL_OPERATOR_STARTS_WITH',
    ),
);
EOQ;
        $this->generateOperatorsFile($generatedFile, $fakeMeta);

        $filters = BeanFactory::getBeanClass('Filters');
        $operators = $filters::getOperators('latrop');

        $this->assertArrayNotHasKey('enum', $operators, 'It should not contain "enum" field type');
        $this->assertArrayNotHasKey('name', $operators, 'It should not contain "name" field type');
        $this->assertArrayHasKey('varchar', $operators, 'It should contain "varchar" field type');
    }

    /**
     * Helper method to write the file and add it to the file map.
     *
     * @param string $file Location of the file.
     * @param string $contents File Contents.
     */
    private function generateOperatorsFile($file, $contents)
    {
        $path = explode('/', $file);
        array_pop($path); // Remove file name
        $path = implode('/', $path);
        SugarTestHelper::ensureDir($path);
        file_put_contents($file, $contents);
    }
}
