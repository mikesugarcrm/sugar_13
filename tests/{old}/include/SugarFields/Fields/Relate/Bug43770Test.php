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

class Bug43770Test extends TestCase
{
    private $fieldOutput;

    protected function setUp(): void
    {
        $sfr = new SugarFieldRelate('relate');
        $vardef = [
            'name' => 'assigned_user_name',
            'id_name' => 'assigned_user_id',
            'module' => 'Users',
        ];
        $displayParams = [
            'idName' => 'Contactsassigned_user_name',
        ];
        $this->fieldOutput = $sfr->getEditViewSmarty('fields', $vardef, $displayParams, 1);
    }

    /**
     * @group   bug43770
     */
    public function testCustomIdName()
    {
        $this->assertStringContainsString(
            'id="Contacts{$fields.assigned_user_name.id_name|escape:\'html\'}"',
            $this->fieldOutput
        );
    }

    public function testCustomIdNameJS()
    {
        $this->assertStringContainsString('"id":"Contactsassigned_user_id"', $this->fieldOutput);
    }
}
