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
 * @coversDefaultClass \PMSEExporter
 */
class PMSEExporterTest extends TestCase
{
    /**
     * Utility method to assemble a bean expectation
     * @return SugarBean
     */
    protected function getBeanForTags()
    {
        $tag1 = $this->createMock('Tag');
        $tag1->method('getRecordName')->willReturn('Tag 1');
        $tag1->id = 'tag1';
        $tag1->name = 'Tag 1';
        $tag1->name_lower = 'tag 1';

        $tag2 = $this->createMock('Tag');
        $tag2->method('getRecordName')->willReturn('Tag 2');
        $tag2->id = 'tag2';
        $tag2->name = 'Tag 2';
        $tag2->name_lower = 'tag 2';

        $bean = $this->createMock('pmse_Project');
        $bean->fetched_row = [
            'id' => 'asdf',
            'name' => 'project def',
        ];

        $bean->method('getTags')
            ->willReturn([
                $tag1->id => $tag1,
                $tag2->id => $tag2,
            ]);

        return $bean;
    }

    /**
     * @covers ::getProject
     */
    public function testGetProject()
    {
        $exporter = $this->getMockBuilder('PMSEExporter')
            ->setMethods(['retrieveBean', 'getMetadata', 'getBean'])
            ->disableOriginalConstructor()
            ->getMock();

        $exporter->method('getBean')
            ->will($this->returnValue($this->getBeanForTags()));

        $result = $exporter->getProject(['id' => '1234']);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('project', $result);
        $this->assertArrayHasKey('tag', $result['project']);
        $this->assertArrayHasKey('tag 1', $result['project']['tag']);
        $this->assertSame($result['project']['tag']['tag 1'], 'Tag 1');

        $result = $exporter->getProject(['id' => '1234', 'project_only' => true]);
        $this->assertArrayNotHasKey('metadata', $result);
        $this->assertArrayHasKey('project', $result);
        $this->assertArrayHasKey('tag', $result['project']);
        $this->assertArrayHasKey('tag 2', $result['project']['tag']);
        $this->assertSame($result['project']['tag']['tag 2'], 'Tag 2');
    }

    /**
     * @covers ::getExporter
     * @dataProvider getExporterData
     * @param $type
     * @param $instance
     */
    public function testGetExporter($type, $instance)
    {
        $exporter = new \PMSEExporter();
        $exporter = $exporter->getExporter($type);

        $this->assertInstanceOf($instance, $exporter);
    }

    public function getExporterData()
    {
        return [
            [
                'name' => 'project',
                'instance' => 'PMSEProjectExporter',
            ],
            [
                'name' => 'business_rule',
                'instance' => 'PMSEBusinessRuleExporter',
            ],
            [
                'name' => 'email_template',
                'instance' => 'PMSEEmailTemplateExporter',
            ],
        ];
    }
}
