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

namespace Sugarcrm\SugarcrmTestsUnit\modules\DataPrivacy;

use PHPUnit\Framework\TestCase;
use Sugarcrm\SugarcrmTestsUnit\TestMockHelper;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 * @coversDefaultClass \DataPrivacy
 */
class DataPrivacyTest extends TestCase
{
    /**
     * @covers ::isStatusChangeAllowed
     *
     * @param $isAdmin
     * @param $oldStatus
     * @param $newStatus
     * @param $expected
     *
     * @dataProvider providerTestIsStatusChangeAllowed
     */
    public function testIsStatusChangeAllowed($isAdmin, $oldStatus, $newStatus, $expected)
    {
        $dataPrivacyMock = TestMockHelper::createMock($this, 'DataPrivacy');
        $result = TestReflection::callProtectedMethod(
            $dataPrivacyMock,
            'isStatusChangeAllowed',
            [$isAdmin, $oldStatus, $newStatus]
        );
        $this->assertEquals($expected, $result);
    }

    public function providerTestIsStatusChangeAllowed()
    {
        return [
            // admin
            [true, null, 'Open', true],
            [true, null, 'Closed', false],
            [true, null, 'Rejected', false],
            [true, 'Open', 'Open', true],
            [true, 'Open', 'Closed', true],
            [true, 'Open', 'Rejected', true],
            [true, 'Closed', 'Open', false],
            [true, 'Rejected', 'Closed', false],
            // non-admin
            [false, null, 'Open', true],
            [false, null, 'Closed', false],
            [false, null, 'Rejected', false],
            [false, 'Open', 'Open', true],
            [false, 'Open', 'Rejected', false],
            [false, 'Closed', 'Open', false],
            [false, 'Open', 'Closed', false],
            [false, 'Rejected', 'Closed', false],
        ];
    }
}
