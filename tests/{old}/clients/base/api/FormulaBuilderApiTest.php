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

class FormulaBuilderApiTest extends TestCase
{
    /**
     * @var \FormulaBuilderApi|mixed
     */
    public $formulaBuilderApi;

    protected function setUp(): void
    {
        SugarTestHelper::setUp('current_user');
        $this->formulaBuilderApi = $this->getFormulaBuilderApiMock();
    }

    /**
     * module provider
     * @return array
     */
    public function moduleProvider()
    {
        return [
            ['Accounts'],
            ['Contacts'],
        ];
    }

    /**
     * @dataProvider moduleProvider
     * @param string $module
     * @covers ::getMeta
     */
    public function testGetMeta($module)
    {
        $result = $this->formulaBuilderApi->meta(
            $this->getRestServiceMock(),
            [
                'module' => $module,
                'allowRestricted' => true,
            ]
        );

        $this->assertNotEmpty($result['fields'], 'There are no fields on this module');
        $this->assertNotEmpty($result['relateFields'], 'There are no related fields for this module');
        $this->assertNotEmpty($result['rollupFields'], 'There are no rollup fields for this module');
        $this->assertNotEmpty($result['fieldsTypes'], 'There are no fields defs on this module');
        $this->assertNotEmpty($result['relateModules'], 'There are no modules related with the one provided');
        $this->assertNotEmpty($result['help'], 'Help is not available');
    }

    /**
     * @param null|array $methods
     * @return \FormulaBuilderApi
     */
    protected function getFormulaBuilderApiMock()
    {
        return $this->getMockBuilder('FormulaBuilderApi')
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * @param null|array $methods
     * @return \RestService
     */
    protected function getRestServiceMock()
    {
        return $this->getMockBuilder('RestService')
            ->onlyMethods([])
            ->getMock();
    }
}
