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

class BeanDuplicateCheckTest extends TestCase
{
    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsZero_TheStrategyRemainsFalse()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = [];
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsTwo_TheStrategyRemainsFalse()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = [
            'FilterDuplicateCheck' => [
                'filter_template' => [
                    [
                        'account_name' => [
                            '$starts' => '$account_name',
                        ],
                    ],
                ],
                'ranking_fields' => [
                    [
                        'in_field_name' => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ],
                ],
            ],
            'ExtraDuplicateCheck' => [
                'filter_template' => [
                    [
                        'account_name' => [
                            '$starts' => '$account_name',
                        ],
                    ],
                ],
                'ranking_fields' => [
                    [
                        'in_field_name' => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ],
                ],
            ],
        ];
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_TheStrategyDefinedInTheMetadataIsInvalid_TheStrategyRemainsFalse()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = [
            'Foobar' => [
                'filter_template' => [
                    [
                        'account_name' => [
                            '$starts' => '$account_name',
                        ],
                    ],
                ],
                'ranking_fields' => [
                    [
                        'in_field_name' => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ],
                ],
            ],
        ];
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->getStrategy();
        self::assertFalse($actual, "The strategy should not have been changed from 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testConstructor_MetadataCountIsOne_TheStrategyIsInitializedToTheStrategyDefinedInTheMetadata()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = [
            'FilterDuplicateCheck' => [
                'filter_template' => [
                    [
                        'account_name' => [
                            '$starts' => '$account_name',
                        ],
                    ],
                ],
                'ranking_fields' => [
                    [
                        'in_field_name' => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ],
                ],
            ],
        ];
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $expected = 'FilterDuplicateCheck';
        $actual = $beanDuplicateCheck->getStrategy();
        self::assertInstanceOf(
            $expected,
            $actual,
            "The strategy should have been changed to an instance of '{$expected}'"
        );
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsFalse_ReturnsNull()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = []; // invalid metadata forces the strategy to remain false
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertNull($actual, "BeanDuplicateCheck::findDuplicates should return null when the strategy is 'false'");
    }

    /**
     * @group duplicatecheck
     */
    public function testFindDuplicates_TheStrategyIsValid_TheFindDuplicatesMethodOnTheStrategyIsCalled()
    {
        $bean = $this->createMock(Lead::class);
        $metadata = [
            'DuplicateCheckMock' => [
                'filter_template' => [
                    [
                        'account_name' => [
                            '$starts' => '$account_name',
                        ],
                    ],
                ],
                'ranking_fields' => [
                    [
                        'in_field_name' => 'account_name',
                        'dupe_field_name' => 'account_name',
                    ],
                ],
            ],
        ];
        $beanDuplicateCheck = new BeanDuplicateCheck($bean, $metadata);

        $actual = $beanDuplicateCheck->findDuplicates();
        self::assertTrue($actual, "DuplicateCheckMock::findDuplicates should return 'true'");
    }
}

// need to make sure SugarApi is included when extending DuplicateCheckStrategy to avoid a fatal error

/**
 * Use the following class to test that DuplicateCheckStrategy::findDuplicates is called when it should be.
 */
class DuplicateCheckMock extends DuplicateCheckStrategy
{
    protected function setMetadata($metadata)
    {
    }

    public function findDuplicates()
    {
        return true;
    }
}
