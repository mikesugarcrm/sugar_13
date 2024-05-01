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

class PMSEImporterTest extends TestCase
{
    /**
     * @var PMSEImporter
     */
    protected $object;
    protected $bean;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $row1 = new stdClass();
        $row2 = new stdClass();
        $row3 = new stdClass();
        $row4 = new stdClass();
        $row5 = new stdClass();
        $row1->name = 'Test';
        $row2->name = 'Test (1)';
        $row3->name = 'Email Template';
        $row4->name = 'Email Template (1)';
        $row5->name = 'Email Template (2)';

        $this->object = ProcessManager\Factory::getPMSEObject('PMSEImporter');
        $this->bean = $this->createPartialMock('pmse_EmailsTemplates', ['get_full_list', 'save', 'in_save']);
        $this->bean->table_name = 'pmse_emails_templates';
        $this->bean->expects($this->any())
            ->method('get_full_list')
//            ->with($this->equalTo('Nombre'));
            ->will($this->returnValue([
                '0' => $row1,
                '1' => $row2,
                '2' => $row3,
                '3' => $row4,
                '4' => $row5,
            ]));

        $this->bean->expects($this->any())
            ->method('save')
//            ->with($this->equalTo('Nombre'));
            ->will($this->returnValue('1'));

        $this->object->setBean($this->bean);
        $this->object->setName('name');
    }

    /**
     * @covers PMSEImporter::saveProjectData
     * @todo   Implement testSaveProjectData().
     */
    public function testSaveProjectData()
    {
        $GLOBALS['current_user'] = new stdClass();
        $GLOBALS['current_user']->id = '';
        $project = ['name' => 'Email Templates'];
        $this->bean->in_save = false;
        $result = $this->object->saveProjectData($project);
        $this->assertEquals(1, $result);

        $this->bean->in_save = true;
        $result = $this->object->saveProjectData($project);
        $this->assertFalse($result);
    }

    /**
     * @covers PMSEImporter::getNameWithSuffix
     * @todo   Implement testGetNameWithSuffix().
     */
    public function testGetNameWhitSuffix()
    {
        $names = [
            'Test' => 'Test (3)',
            'Test (1)' => 'Test (1) (3)',
            'Email Template' => 'Email Template (3)',
            'Email Template (1)' => 'Email Template (1) (3)',
            'Email Template (2)' => 'Email Template (2) (2)',
            'Emails Templates' => 'Emails Templates',
        ];

        foreach ($names as $key => $value) {
            $result = $this->object->getNameWithSuffix($key);
            $this->assertEquals($value, $result);
        }
    }

    public function saveProjectDataProvider()
    {
        return [
            [
                'keepIds' => false,
                'beanId' => 'new-et-id-1',
                'projectId' => 'old-prj-id-1',
                'expect' => 'new-et-id-1',
            ],
            [
                'keepIds' => true,
                'beanId' => 'new-et-id-2',
                'projectId' => 'old-prj-id-2',
                'expect' => 'old-prj-id-2',
            ],
        ];
    }

    /**
     * @covers       PMSEImporter::saveProjectDataWithOptions
     * @dataProvider saveProjectDataProvider
     */
    public function testSaveProjectDataWithOptions($keepIds, $beanId, $projectId, $expect)
    {
        // Mock an Email Template bean
        $bean = $this->createPartialMock('pmse_Emails_Templates', ['save']);
        $bean->id = $beanId;

        // Get the Email Template importer for testing
        $importer = ProcessManager\Factory::getPMSEObject('PMSEEmailTemplateImporter');

        // Set the bean and some options onto the importer
        $importer->setBean($bean);
        $importer->setOptions(
            [
                'keepName' => true,
                'keepIds' => $keepIds,
            ]
        );

        // Run it, testing for a NEW id from the bean
        $result = $importer->saveProjectData(
            [
                'id' => $projectId,
                'name' => 'et',
            ]
        );

        // Assertions
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
        $this->assertSame($result['id'], $expect);
    }
}
