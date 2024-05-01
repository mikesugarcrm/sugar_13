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

use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \PMSEImporter
 */
class PMSEImporterTest extends TestCase
{
    /**
     * @var \User
     */
    protected $currentUserBackUp;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (!empty($GLOBALS['current_user'])) {
            $this->currentUserBackUp = $GLOBALS['current_user'];
        }
        $GLOBALS['current_user'] = $this->createMock(\User::class);
        $GLOBALS['current_user']->user_name = 'dump_user_name';
        $GLOBALS['current_user']->id = 'user';
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->currentUserBackUp) {
            $GLOBALS['current_user'] = $this->currentUserBackUp;
        } else {
            unset($GLOBALS['current_user']);
        }
    }

    /**
     * @covers PMSEImporter::setOption
     * @covers PMSEImporter::getOption
     */
    public function testSetAndGetOption()
    {
        // Add some options to test with
        $importer = ProcessManager\Factory::getPMSEObject('PMSEImporter');
        $importer->setOption('foo', 'bar');
        $importer->setOption('baz', 'zim');

        // Test collecting options
        $this->assertSame('bar', $importer->getOption('foo'));
        $this->assertSame('zim', $importer->getOption('baz'));

        // Test defaults
        $this->assertNull($importer->getOption('bad_name'));
        $this->assertSame(false, $importer->getOption('bad_name', false));
        $this->assertSame('testy', $importer->getOption('bad_name', 'testy'));
    }

    /**
     * @covers PMSEImporter::setOptions
     * @covers PMSEImporter::getOption
     * @covers PMSEImporter::getOptions
     */
    public function testSetAndGetOptions()
    {
        // Add some options to test with
        $importer = ProcessManager\Factory::getPMSEObject('PMSEImporter');
        $importer->setOption('foo', 'bar');

        // Test collecting options
        $this->assertSame('bar', $importer->getOption('foo'));

        // Set new options that will override
        $importer->setOptions([
            'bar' => 'baz',
            'boo' => 'box',
        ]);

        // Test options were overridden
        $this->assertNull($importer->getOption('foo'));

        // Test our options being set
        $this->assertSame('baz', $importer->getOption('bar'));
        $this->assertSame('box', $importer->getOption('boo'));

        // Test appending options
        $importer->setOptions([
            'fox' => 'sox',
            'egg' => 'meg',
        ], true);

        // Test our options were kept
        $this->assertSame('baz', $importer->getOption('bar'));
        $this->assertSame('box', $importer->getOption('boo'));

        // Test new options were set
        $this->assertSame('sox', $importer->getOption('fox'));
        $this->assertSame('meg', $importer->getOption('egg'));

        // For testing getOptions
        $options = [
            'bar' => 'baz',
            'boo' => 'box',
            'fox' => 'sox',
            'egg' => 'meg',
        ];

        $this->assertSame($options, $importer->getOptions());
    }

    /**
     * @covers PMSEImporter::importDependencies
     */
    public function testImportDependencies()
    {
        $importerMock = $this->getMockBuilder('PMSEImporter')
            ->setMethods([
                'getBean',
                'unsetCommonFields',
                'getNameWithSuffix',
                'saveProjectActivitiesData',
                'saveProjectEventsData',
                'saveProjectGatewaysData',
                'saveProjectElementsData',
                'saveProjectFlowsData',
                'processDefaultFlows',
                'processImport',
            ])
            ->getMock();
        $importerMock->method('processImport')->willReturn(['id' => 'newId1']);
        $dependencies = [
            'business_rule' => [['id' => 'oldId1', 'name' => 'br1']],
        ];
        $selectedIds = ['oldId1'];
        $importerMock->importDependencies($dependencies, $selectedIds);
        $dependencyKeys = $importerMock->getDependencyKeys();
        $this->assertArrayHasKey('oldId1', $dependencyKeys);
    }
}
