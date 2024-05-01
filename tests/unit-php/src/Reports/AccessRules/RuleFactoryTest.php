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

namespace Sugarcrm\SugarcrmTestsUnit\src\Reports\AccessRules;

use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\Reports\AccessRules\RuleFactory;
use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\ConfigExportRule;
use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\ViewRightsRule;
use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\AccessFieldsRightsRule;
use Sugarcrm\Sugarcrm\Reports\AccessRules\Rules\ExportRightsRule;
use User;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Reports\AccessRules\RuleFactory
 */
class RuleFactoryTest extends TestCase
{
    /**
     * getRule function
     */
    public function testGetRule()
    {
        global $current_user;
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAdmin'])
            ->getMock();
        $userMock->expects($this->any())
            ->method('isAdmin')
            ->will($this->returnValue(true));
        $current_user = $userMock;

        $rule = RuleFactory::getRule('configExport', $current_user);
        $this->assertInstanceOf(ConfigExportRule::class, $rule);

        $rule = RuleFactory::getRule('viewRights', $current_user);
        $this->assertInstanceOf(ViewRightsRule::class, $rule);

        $rule = RuleFactory::getRule('accessFieldsRights', $current_user);
        $this->assertInstanceOf(AccessFieldsRightsRule::class, $rule);

        $rule = RuleFactory::getRule('exportRights', $current_user);
        $this->assertInstanceOf(ExportRightsRule::class, $rule);
    }
}
