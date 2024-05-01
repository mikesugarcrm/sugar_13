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

namespace Sugarcrm\SugarcrmTestsUnit\modules\pmse_Inbox\engine;

use PHPUnit\Framework\TestCase;

require_once 'modules/pmse_Inbox/engine/PMSEFieldsUtils.php';

class PMSEFieldsUtilsTest extends TestCase
{
    protected $oldBwcModules = null;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->oldBwcModules = $GLOBALS['bwcModules'] ?? null;
        $GLOBALS['bwcModules'] = ['Employees', 'Documents'];
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        if (!empty($this->oldBwcModules)) {
            $GLOBALS['bwcModules'] = $this->oldBwcModules;
        }
        parent::tearDown();
    }

    /**
     * @covers ::bpminbox_get_href
     * @param bool $hasBean
     * @param null|string $name
     * @param null|string $value
     * @param string $expected
     * @dataProvider getLinkData
     */
    public function testBpminbox_get_href(bool $hasBean, ?string $name, ?string $value, string $expected)
    {
        if ($hasBean) {
            $bean = $this->getMockBuilder('Contacts')
                ->setMethods(['getRecordName'])
                ->disableOriginalConstructor()
                ->getMock();

            $bean->method('getRecordName')
                ->will($this->returnValue('John Doe'));

            $bean->id = '12345';
            $bean->module_dir = 'Contacts';
            $bean->module_name = 'Contacts';
        } else {
            $bean = null;
        }
        $link = bpminbox_get_href($bean, $name, $value);
        $this->assertEquals($expected, $link);
    }

    public function getLinkData()
    {
        return [
            [
                'hasBean' => true,
                'name' => 'name',
                'value' => 'Task123',
                'expected' => '<a href="' . \SugarConfig::getInstance()->get('site_url') .
                    '/index.php#Contacts/12345">John Doe</a>',
            ],
            [
                'hasBean' => false,
                'name' => null,
                'value' => null,
                'expected' => '',
            ],
        ];
    }
}
