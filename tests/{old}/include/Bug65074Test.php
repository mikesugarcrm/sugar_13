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

require_once 'include/utils.php';

function testFuncString()
{
    return 'func string';
}

function testFuncArgs($args)
{
    return $args;
}

class testBeanParam
{
    public function testFuncBean()
    {
        return 'func bean';
    }
}

/**
 * @ticket 65074
 */
class Bug65074Test extends TestCase
{
    protected $customIncludeDir = 'custom/include';
    protected $customIncludeFile = 'bug65074_include.php';

    protected function setUp(): void
    {
        // create a custom include file
        $customIncludeFileContent = <<<EOQ
<?php
function testFuncInclude()
{
        return 'func include';
}
EOQ;
        if (!file_exists($this->customIncludeDir)) {
            sugar_mkdir($this->customIncludeDir, 0777, true);
        }

        file_put_contents($this->customIncludeDir . '/' . $this->customIncludeFile, $customIncludeFileContent);
    }

    protected function tearDown(): void
    {
        // remove the custom include file
        if (file_exists($this->customIncludeDir . '/' . $this->customIncludeFile)) {
            unlink($this->customIncludeDir . '/' . $this->customIncludeFile);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testGetFunctionValue
     */
    public function dataProviderForTestGetFunctionValue()
    {
        return [
            [null, 'testFuncString', [], 'func string'],
            [null, 'testFuncArgs', ['func args'], 'func args'],
            [new testBeanParam(), 'testFuncBean', [], 'func bean'],
            ['', ['name' => 'testFuncInclude', 'include' => $this->customIncludeDir . '/' . $this->customIncludeFile], [], 'func include'],
            [null, ['name' => ['Scheduler', 'getJobsList']], [], Scheduler::getJobsList()],
        ];
    }

    /**
     * Tests function getFunctionValue()
     * @dataProvider dataProviderForTestGetFunctionValue
     */
    public function testGetFunctionValue($bean, $function, $args, $value)
    {
        $this->assertEquals($value, getFunctionValue($bean, $function, $args), 'Function getFunctionValue() returned wrong result.');
    }
}
