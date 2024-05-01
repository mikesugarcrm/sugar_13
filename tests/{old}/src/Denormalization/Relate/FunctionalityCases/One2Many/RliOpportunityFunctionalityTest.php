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

namespace Sugarcrm\SugarcrmTests\Denormalization\Relate\FunctionalityCases\One2Many;

use SugarBean;
use SugarTestOpportunityUtilities;
use SugarTestRevenueLineItemUtilities;

class RliOpportunityFunctionalityTest extends AbstractFunctionalityTest
{
    protected static $options = [
        'primary_module' => 'RevenueLineItems',
        'field_id' => 'opportunity_id',
        'field_name' => 'opportunity_name',
        'relate_field_name' => 'name',
        'primary_link_name' => 'opportunities',
    ];

    protected function createPrimaryBean(?SugarBean $linkedBean): SugarBean
    {
        $mainBean = SugarTestRevenueLineItemUtilities::createRevenueLineItem();

        if ($linkedBean) {
            $mainBean->opportunity_id = $linkedBean->id;
            $mainBean->save();
        }

        return $mainBean;
    }

    protected function createLinkedBean(): SugarBean
    {
        return SugarTestOpportunityUtilities::createOpportunity();
    }

    protected static function removeCreatedBeans(): void
    {
        SugarTestOpportunityUtilities::removeAllCreatedOpportunities();
        SugarTestRevenueLineItemUtilities::removeAllCreatedRevenueLineItems();
    }
}
