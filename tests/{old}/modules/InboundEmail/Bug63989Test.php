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

/**
 * @ticket 63989
 */
class Bug63989Test extends TestCase
{
    /**
     * @var aCase
     */
    private $case;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        $this->case = BeanFactory::newBean('Cases');
    }

    protected function tearDown(): void
    {
        /** @var DBManager */
        global $db;
        if ($this->case && $this->case->id) {
            $query = 'DELETE FROM cases where id = ' . $db->quoted($this->case->id);
            $db->query($query);
        }
    }

    public function testGetCaseIdFromCaseNumber()
    {
        $this->case->save();
        $id = $this->case->id;

        $this->case->disable_row_level_security = true;
        $this->case->retrieve($id);
        $number = $this->case->case_number;

        $ie = new InboundEmail();
        $subject = '[CASE:' . $number . ']';
        $actual_id = $ie->getCaseIdFromCaseNumber($subject, $this->case);

        $this->assertEquals($id, $actual_id);

        $subject = '[CASE: ' . $number . ']';
        $actual_id = $ie->getCaseIdFromCaseNumber($subject, $this->case);

        $this->assertEquals($id, $actual_id);
    }

    /**
     * @param $emailName
     * @dataProvider shouldNotQueryProvider
     */
    public function testShouldNotQuery($emailName)
    {
        $db = $this->getMockForAbstractClass('DBManager');
        $db->expects($this->never())
            ->method('query');

        $ie = new InboundEmail();
        $ie->db = $db;
        $ie->getCaseIdFromCaseNumber($emailName, $this->case);
    }

    public function shouldNotQueryProvider()
    {
        return [
            ['An arbitrary subject'],
            ['[CASE:THE-CASE-NUMBER]'],
        ];
    }
}
