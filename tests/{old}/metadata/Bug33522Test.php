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
 * @ticket 33522
 */
class Bug33522Test extends TestCase
{
    public function testCheckMetadataRelationshipNames()
    {
        $dictionary = [];
        $ar = new TestAbstractRelationships();
        $errMsg = 'Relationship key discrepancy exists with key not being in AbstractRelationships->specialCaseBaseNames.';

        $specialCaseBaseNames = $ar->getSpecialCaseBaseNames();

        // load all files from metadata/ that could potentially have
        // relationships in them
        foreach (glob('metadata/*.php') as $filename) {
            include $filename;
        }

        // load all relationships into AbstractRelationships->relationships
        foreach ($dictionary as $key => $val) {
            if (isset($dictionary[$key]['relationships'])) {
                $relationships = $dictionary[$key]['relationships'];
                foreach ($relationships as $relKey => $relVal) {
                    // if our key and relationship key are not equal
                    // check to make sure the key is in the special list
                    // otherwise we may have relationship naming issues down the road
                    if ($key !== $relKey) {
                        $this->assertContains($key, $specialCaseBaseNames, $errMsg);
                    }
                }
            }
        }
    }
}

class TestAbstractRelationships extends AbstractRelationships
{
    public function getSpecialCaseBaseNames()
    {
        return $this->specialCaseBaseNames;
    }
}
