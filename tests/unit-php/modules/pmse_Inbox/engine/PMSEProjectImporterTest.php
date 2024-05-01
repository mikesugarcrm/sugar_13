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
 * @coversDefaultClass \PMSEImporter
 */
class PMSEProjectImporterTest extends TestCase
{
    /**
     * @param $field
     * @dataProvider addDependencyIdToDefinitionProvider
     * @covers ::getDependencyKeys
     */
    public function testAddDependencyIdToDefinition($field, $id, $importedId)
    {
        $definition = [];
        $definition[$field] = $id;
        $projectImporterMock = $this->getMockBuilder('PMSEProjectImporter')
            ->setMethods(['getDependencyKeys'])
            ->getMock();
        $projectImporterMock->method('getDependencyKeys')->willReturn(['old' => $importedId]);
        $definition = $projectImporterMock->addDependencyIdToDefinition($definition, $field);
        $this->assertEquals($definition[$field], $importedId);
    }

    public function addDependencyIdToDefinitionProvider()
    {
        return [
            [
                'field' => 'act_fields',
                'id' => 'old',
                'importedId' => 'new',
            ],
            [
                'field' => 'evn_criteria',
                'id' => 'not imported',
                'importedId' => '',
            ],
        ];
    }
}
