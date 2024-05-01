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

/**
 * @covers SidecarFilterLayoutMetaDataParser
 */
class SidecarFilterLayoutMetaDataParserTest extends TestCase
{
    public function testRemoveExistingField()
    {
        $parser = $this->getRemoveFieldMock();
        $result = $parser->removeField('field1');
        $this->assertTrue($result, 'The field should have been successfully removed');
        $this->assertArrayNotHasKey('field1', $parser->_viewdefs, 'The field should not be contained in metadata');
    }

    public function testRemoveNonExistingField()
    {
        $parser = $this->getRemoveFieldMock();
        $result = $parser->removeField('field2');
        $this->assertFalse($result, 'The field should not have been removed');
    }

    /**
     * @return SidecarFilterLayoutMetaDataParser
     */
    private function getRemoveFieldMock()
    {
        /** @var SidecarFilterLayoutMetaDataParser $parser */
        $parser = $this->getMockBuilder('SidecarFilterLayoutMetaDataParser')
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $parser->_viewdefs = [
            'fields' => [
                'field1' => [],
            ],
        ];

        return $parser;
    }
}
