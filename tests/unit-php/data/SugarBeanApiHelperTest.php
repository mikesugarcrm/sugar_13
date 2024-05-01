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

namespace Sugarcrm\SugarcrmTestsUnit\data;

use PHPUnit\Framework\TestCase;
use ServiceBase;
use SugarBean;
use SugarBeanApiHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass SugarBeanApiHelper
 */
class SugarBeanApiHelperTest extends TestCase
{
    /**
     * @covers ::formatForApi
     * @dataProvider providerFormatForApi
     * @param string $action Attempted ACL action.
     * @param bool $aclAccess Do we have access to this bean for the given
     *   action at all?
     * @param array $fieldDefs Field definitions.
     * @param array $fieldValueMap Result data, pre-ACL.
     * @param array $fieldAccessMap Field-level ACLs.
     * @param array $beanAcl Bean-level ACL's.
     * @param array $expected Expected output.
     */
    public function testFormatForApi(
        string $action,
        bool   $aclAccess,
        array  $fieldDefs,
        array  $fieldValueMap,
        array  $fieldAccessMap,
        array  $beanAcl,
        array  $expected
    ) {

        $numFields = count($fieldAccessMap);

        $api = $this->createPartialMock(ServiceBase::class, ['execute', 'handleException']);
        $apiHelper = $this->createPartialMock(SugarBeanApiHelper::class, ['getBeanAcl']);
        TestReflection::setProtectedValue($apiHelper, 'api', $api);

        $bean = $this->createPartialMock(SugarBean::class, ['ACLAccess', 'ACLFieldAccess']);

        $bean->expects($this->once())
            ->method('ACLAccess')
            ->with($action)
            ->willReturn($aclAccess);
        $bean->expects($this->exactly($numFields))
            ->method('ACLFieldAccess')
            ->with($this->anything(), 'read')
            ->willReturnCallback(function ($field, $action) use ($fieldAccessMap) {
                return $fieldAccessMap[$field];
            });

        $bean->field_defs = $fieldDefs;
        $bean->deleted = false;
        foreach ($fieldValueMap as $key => $value) {
            $bean->$key = $value;
        }

        $apiHelper->expects($this->once())
            ->method('getBeanAcl')
            ->willReturn($beanAcl);

        $actual = $apiHelper->formatForApi($bean, array_keys($fieldAccessMap), ['action' => $action]);
        $this->assertEquals($expected, $actual);
    }

    public function providerFormatForApi(): array
    {
        return [
            // basic test: list action, just one field, no restrictions
            [
                'list',
                true,
                [
                    'myfield' => [
                        'name' => 'myfield',
                        'type' => 'varchar',
                    ],
                ],
                [
                    'myfield' => 'myvalue',
                ],
                [
                    'myfield' => true,
                ],
                [
                    'fields' => (object)[],
                ],
                // expected results
                [
                    'myfield' => 'myvalue',
                    '_acl' => [
                        'fields' => (object)[],
                    ],
                ],
            ],
        ];
    }
}
