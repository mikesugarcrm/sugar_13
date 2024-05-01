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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

class PMSEHistoryDataTest extends TestCase
{
    private $module = 'Leads';

    /**
     * @var PMSEHistoryData
     */
    private $object;

    protected function setUp(): void
    {
        $this->object = ProcessManager\Factory::getPMSEObject('PMSEHistoryData');
        $this->object->setModule($this->module);
    }

    public function testSavePredata()
    {
        $value = [];
        $value['before_data'][1] = 'Ok';
        $this->object->savePredata(1, 'Ok');
        $logData = $this->object->getLog();
        $this->assertEquals($value['before_data'], $logData['before_data']);
    }

    public function testSavePostData()
    {
        $value = [];
        $value['after_data'][1] = 'Ok';
        $this->object->savePostData(1, 'Ok');
        $logData = $this->object->getLog();
        $this->assertEquals($value['after_data'], $logData['after_data']);
    }

    public function testVerifyRepeated()
    {
        $value = [];
        $value['after_data'][1] = 'Ok';
        $this->object->verifyRepeated('Ok', 'Ok');

        $this->assertTrue(SugarTestReflection::getProtectedValue($this->object, 'repeated'));
    }

    public function testLock()
    {
        $value = 'conditons';
        $this->object->lock($value);

        $this->assertSame($value, SugarTestReflection::getProtectedValue($this->object, 'lock'));
    }
}
