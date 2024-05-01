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

namespace Sugarcrm\SugarcrmTestsUnit\Calendar;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Calendar\QueryManager;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Calendar\QueryManager
 */
class QueryManagerTest extends TestCase
{
    /**
     * @covers ::prepareDbFieldForQuery
     */
    public function testPrepareDbFieldForQuery(): void
    {
        $filterManagerClass = $this->createPartialMock(
            QueryManager::class,
            ['getFieldProperty']
        );

        $callBean = $this->createPartialMock('Call', [
            'getTableName',
        ]);

        $callBean->method('getTableName')->willReturn('calls');

        $filterManagerClass->method('getFieldProperty')->willReturn('custom_fields');

        $filterManagerClass->targetBean = $callBean;
        $filterManagerClass->targetBean->module_dir = 'Calls';
        $filterManagerClass->targetBean->table_name = 'calls';

        $result = $filterManagerClass->prepareDbFieldForQuery('fieldname_c');

        $this->assertEquals($result, 'calls_cstm.fieldname_c');
    }

    public function allKeysAreNumericProvider(): array
    {
        return [
            [
                [1],
                true,
            ],
            [
                ['$owner'],
                false,
            ],
        ];
    }

    /**
     * @covers ::allKeysAreNumeric
     * @dataProvider allKeysAreNumericProvider
     */
    public function testAllKeysAreNumeric($input, $expected): void
    {

        $queryManagerClass = $this->createPartialMock(
            QueryManager::class,
            []
        );

        $result = $queryManagerClass->allKeysAreNumeric($input);

        $this->assertEquals($result, $expected);
    }
}
