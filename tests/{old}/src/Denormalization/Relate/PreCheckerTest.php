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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate;

use BeanFactory;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Db\Db;
use Sugarcrm\Sugarcrm\Denormalization\Relate\FieldConfig;
use Sugarcrm\Sugarcrm\Denormalization\Relate\PreChecker;
use Sugarcrm\Sugarcrm\Denormalization\Relate\Process\Entity;
use Sugarcrm\Sugarcrm\Denormalization\Relate\SynchronizationManager;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use SugarTestHelper;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Denormalization\Relate\PreChecker
 */
class PreCheckerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers ::validateDenormalization
     */
    public function testValidateDenormalizationNonSupportedType()
    {
        $expectedValidationError = 'LBL_MANAGE_RELATE_DENORMALIZATION_PRECHECK_SUPPORTED_TYPES';

        $fieldDef = [
            'name' => 'test',
            'type' => 'not_supported',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker();
        $result = $preChecker->validateDenormalization($entity);

        $this->assertArrayHasKey('validation_error', $result);
        $this->assertEquals($expectedValidationError, $result['validation_error']);
    }

    /**
     * @covers ::validateDenormalization
     */
    public function testValidateDenormalizationAlreadyDenormalized()
    {
        $expectedValidationError = 'LBL_MANAGE_RELATE_DENORMALIZATION_PRECHECK_ALREADY_DENORMALIZED';

        $fieldDef = [
            'name' => 'test',
            'type' => 'relate',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker();
        $result = $preChecker->validateDenormalization($entity);

        $this->assertArrayHasKey('validation_error', $result);
        $this->assertEquals($expectedValidationError, $result['validation_error']);
    }

    /**
     * @covers ::validateDenormalization
     */
    public function testValidateDenormalizationJobInProgress()
    {
        $expectedValidationError = 'LBL_MANAGE_RELATE_DENORMALIZATION_PRECHECK_PREV_JOB_IN_PROGRESS';

        $fieldDef = [
            'name' => 'test',
            'type' => 'relate',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker(false);
        $result = $preChecker->validateDenormalization($entity);

        $this->assertArrayHasKey('validation_error', $result);
        $this->assertEquals($expectedValidationError, $result['validation_error']);
    }

    /**
     * @covers ::validateDenormalization
     */
    public function testValidateDenormalizationReturnValue()
    {
        $fieldDef = [
            'name' => 'test',
            'type' => 'relate',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker(false, false);

        // relationship is undefined so empty array returns
        $validationDetails = $preChecker->validateDenormalization($entity);
        $this->assertEmpty($validationDetails);

        // relationship is populated
        $entity = $this->getEntity($fieldDef, true);
        $preChecker = $this->getPreChecker(false, false);

        $validationDetails = $preChecker->validateDenormalization($entity);

        $expected = [
            'table_rel' => 'test_join_table',
            'table_lhs' => 'test_lhs_table',
            'table_rhs' => 'test_rhs_table',
            'count_rel' => 1,
            'count_lhs' => 2,
            'count_rhs' => 3,
            'update_count' => 3,
            'estimated_time' => 0,
            'sql' => 'some sql',
        ];

        $this->assertEquals($expected, $validationDetails);
    }

    /**
     * @covers ::validateNormalization
     */
    public function testValidateNormalizationNonSupportedType()
    {
        $expectedValidationError = 'LBL_MANAGE_RELATE_DENORMALIZATION_PRECHECK_SUPPORTED_TYPES';

        $fieldDef = [
            'name' => 'test',
            'type' => 'not_supported',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker();
        $result = $preChecker->validateNormalization($entity);

        $this->assertArrayHasKey('validation_error', $result);
        $this->assertEquals($expectedValidationError, $result['validation_error']);
    }

    /**
     * @covers ::validateNormalization
     */
    public function testValidateDenormalizatioNotDenormalized()
    {
        $expectedValidationError = 'LBL_MANAGE_RELATE_DENORMALIZATION_PRECHECK_FIELD_IS_NOT_DENORMALIZED';

        $fieldDef = [
            'name' => 'test',
            'type' => 'relate',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker(false);
        $result = $preChecker->validateNormalization($entity);

        $this->assertArrayHasKey('validation_error', $result);
        $this->assertEquals($expectedValidationError, $result['validation_error']);
    }

    /**
     * @covers ::validateNormalization
     */
    public function testValidateNormalizationReturnValue()
    {
        $fieldDef = [
            'name' => 'test',
            'type' => 'relate',
        ];
        $entity = $this->getEntity($fieldDef);
        $preChecker = $this->getPreChecker(true);
        $validationDetails = $preChecker->validateNormalization($entity);

        $this->assertEquals(['sql' => 'some sql'], $validationDetails);
    }

    private function getPreChecker(
        bool $setAsDenormalized = true,
        bool $syncJobIsInProgress = true
    ): PreChecker {

        $preChecker = new PreChecker();

        TestReflection::setProtectedValue(
            $preChecker,
            'synchronizationJob',
            $this->getSynchronizationJobMock($syncJobIsInProgress)
        );

        TestReflection::setProtectedValue($preChecker, 'db', $this->getDbMock());
        TestReflection::setProtectedValue($preChecker, 'fieldConfig', $this->getFieldConfigMock($setAsDenormalized));

        return $preChecker;
    }

    private function getSynchronizationJobMock(bool $isInProgress)
    {
        $mock = $this->createPartialMock(SynchronizationManager::class, ['isJobInProgress']);
        $mock->expects($this->any())->method('isJobInProgress')->willReturn($isInProgress);

        return $mock;
    }

    private function getEntity(array $fieldDef, bool $isRelationshipPopulated = false): Entity
    {
        $fieldDef['module'] = 'Opportunities';
        $fieldDef['source'] = 'test_source';
        $fieldDef['rname'] = 'test_rname';

        $entity = new Entity(BeanFactory::getBean('Contacts'), '');

        $entity->setFieldDef($fieldDef);

        $entity->fieldDefExt = [
            'denorm_test' => [],
        ];

        if ($isRelationshipPopulated) {
            $entity->relationship = $this->getRelationshipMock();
        }

        return $entity;
    }

    private function getFieldConfigMock(bool $setAsDenormalized)
    {
        $mock = $this->createPartialMock(FieldConfig::class, ['getList']);

        $mock->expects($this->any())
            ->method('getList')
            ->willReturn(['Contacts' => ['test' => $setAsDenormalized ? [1] : null]]);

        return $mock;
    }

    private function getRelationshipMock()
    {
        $mock = $this->getMockForAbstractClass(\SugarRelationship::class);
        TestReflection::setProtectedValue(
            $mock,
            'def',
            [
                'join_table' => 'test_join_table',
                'lhs_table' => 'test_lhs_table',
                'rhs_table' => 'test_rhs_table',
            ]
        );

        return $mock;
    }

    private function getDbMock()
    {
        $mock = $this->getMockForAbstractClass(
            Db::class,
            [],
            '',
            false,
            true,
            true,
            ['getTableRowCount', 'getTableDescription', 'getAlterSql']
        );

        $mock->expects($this->any())->method('getTableRowCount')->willReturn(1, 2, 3);
        $mock->expects($this->any())->method('getAlterSql')->willReturn('some sql');

        return $mock;
    }
}
