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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\AccessRules\Rules;

use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\ConfigExportRule;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\Exception\SugarReportsExceptionDisabledExport;
use User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\AccessRules\ConfigExportRule
 */
class ConfigExportRuleTest extends TestCase
{
    /**
     * @var
     */
    protected $initialDisableExport;

    /**
     * @var
     */
    protected $initialAdminExportOnly;

    /**
     * User
     */
    protected $userMock;

    /**
     * string
     */
    protected $userIdTest = 'testUser';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        global $sugar_config;
        $this->initialDisableExport = isset($sugar_config['disable_export']) ?? null;
        $this->initialAdminExportOnly = isset($sugar_config['admin_export_only']) ?? null;

        $this->userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAdmin'])
            ->getMock();
        $this->userMock->id = $this->userIdTest;
        $this->userMock->is_admin = '1';
        $this->userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        global $sugar_config;
        if (!is_null($this->initialDisableExport)) {
            $sugar_config['disable_export'] = $this->initialDisableExport;
        }
        if (!is_null($this->initialAdminExportOnly)) {
            $sugar_config['admin_export_only'] = $this->initialAdminExportOnly;
        }
    }

    /**
     * validate function
     */
    public function testValidate()
    {
        global $sugar_config;
        $savedReportMock = $this->getMockBuilder(Report::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rule = new ConfigExportRule($this->userMock);

        $sugar_config['admin_export_only'] = false;

        $sugar_config['disable_export'] = false;
        $validationResult = $rule->validate($savedReportMock);
        $this->assertTrue($validationResult);

        $sugar_config['disable_export'] = true;
        $this->expectException(SugarReportsExceptionDisabledExport::class);
        $rule->validate($savedReportMock);
    }
}
