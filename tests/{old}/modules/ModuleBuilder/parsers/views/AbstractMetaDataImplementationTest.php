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

class AbstractMetaDataImplementationTest extends TestCase
{
    /**
     * @covers ::setFieldProps
     * @dataProvider setFieldPropsDataProvider
     *
     * @param array $constructorArgs Array of parameters to be passed in constructor call
     * @param array $fieldNames Array of field names to which the given properties will be set
     * @param array $propertyList Array of 'property => value' to be set for each field in
     * @param array $viewdefs viewdefs to be passed in constructor call
     * @param array $expectedViewdefs expected viewdefs
     * @throws Exception Thrown if the provided view doesn't exist for this module
     */
    public function testSetFieldProps($constructorArgs, $fieldNames, $propertyList, $viewdefs, $expectedViewdefs)
    {
        $impl = SugarTestAbstractMetaDataImplementationUtilities::createDeployedMetaDataImplementation(
            $viewdefs,
            $constructorArgs
        );
        $impl->setFieldProps($fieldNames, $propertyList);
        $this->assertEquals($expectedViewdefs, $impl->getViewdefs());

        // unsets the implementation created above
        SugarTestAbstractMetaDataImplementationUtilities::removeCreatedImplementation($impl);
    }

    public function setFieldPropsDataProvider()
    {
        return [
            [
                // contructorArgs
                ['wirelesseditview', 'Opportunities', 'mobile'],
                // fieldNames
                [
                    'date_closed',
                    'service_start_date',
                    'test_2',
                ],
                // propertyList
                [
                    'newProperty' => 'newValue',
                    'readonly' => true,
                ],
                // viewdefs
                [
                    'mobile' => [
                        'view' => [
                            'edit' => [
                                'panels' => [
                                    0 => [
                                        'fields' => [
                                            0 => 'date_closed',
                                            1 => [
                                                'name' => 'service_start_date',
                                                'label' => 'TEST_LBL',
                                            ],
                                            2 => 'test_1',
                                            3 => [
                                                'name' => 'test_2',
                                                'readonly' => false,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                // expectation
                [
                    'mobile' => [
                        'view' => [
                            'edit' => [
                                'panels' => [
                                    0 => [
                                        'fields' => [
                                            0 => [
                                                'name' => 'date_closed',
                                                'newProperty' => 'newValue',
                                                'readonly' => true,
                                            ],
                                            1 => [
                                                'name' => 'service_start_date',
                                                'label' => 'TEST_LBL',
                                                'newProperty' => 'newValue',
                                                'readonly' => true,
                                            ],
                                            2 => 'test_1',
                                            3 => [
                                                'name' => 'test_2',
                                                'newProperty' => 'newValue',
                                                'readonly' => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @covers AbstractMetaDataImplementation::_loadFromFile
     */
    public function testLoadFromFile()
    {
        $testObject = new class extends AbstractMetaDataImplementation {
            protected $_view = 'listview';
            public function loadFromFile($file)
            {
                return $this->_loadFromFile($file);
            }
        };

        $tmpFile = SUGAR_PATH . '/cache/AbstractMetaDataImplementationTestTmpFile.php';
        file_put_contents($tmpFile, '<?php $listViewDefs = null;');

        // this caused PHP Warning on PHP < 8.2:
        // current() expects parameter 1 to be array, null given
        // reset() expects parameter 1 to be array, null given
        // But should work on PHP >= 8.2 as we catch TypeError
        $result = $testObject->loadFromFile($tmpFile);

        $this->assertNull($result);
    }
}
