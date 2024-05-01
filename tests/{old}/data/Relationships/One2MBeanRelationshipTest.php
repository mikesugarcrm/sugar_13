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

class One2MBeanRelationshipTest extends TestCase
{
    protected function tearDown(): void
    {
        SugarTestKBContentUtilities::removeAllCreatedBeans();
    }

    public function testProperRhsFieldIsSet()
    {
        $primaryBean = SugarTestKBContentUtilities::createBean([
            'kbdocument_id' => create_guid(),
        ]);
        $relatedBean = SugarTestKBContentUtilities::createBean();

        $primaryBean->load_relationship('localizations');
        $primaryBean->localizations->add($relatedBean);

        $this->assertEquals($primaryBean->kbdocument_id, $relatedBean->kbdocument_id);
    }
}
