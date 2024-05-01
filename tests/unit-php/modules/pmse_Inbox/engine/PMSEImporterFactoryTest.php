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

/**
 * @coversDefaultClass \PMSEImporterFactory
 */
class PMSEImporterFactoryTest extends TestCase
{
    /**
     * @covers ::getImporter
     * @dataProvider getImporterData
     * @param $type
     * @param $instance
     */
    public function testGetImporter($type, $instance)
    {
        $importer = \PMSEImporterFactory::getImporter($type);
        $this->assertInstanceOf($instance, $importer);
    }


    public function getImporterData()
    {
        return [
            [
                'name' => 'project',
                'instance' => 'PMSEProjectImporter',
            ],
            [
                'name' => 'business_rule',
                'instance' => 'PMSEBusinessRuleImporter',
            ],
            [
                'name' => 'businessRule',
                'instance' => 'PMSEBusinessRuleImporter',
            ],
            [
                'name' => 'email_template',
                'instance' => 'PMSEEmailTemplateImporter',
            ],
            [
                'name' => '',
                'instance' => 'PMSEImporter',
            ],
        ];
    }
}
