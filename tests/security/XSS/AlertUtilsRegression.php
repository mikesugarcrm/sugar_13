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
declare(strict_types=1);

use Regression\SugarCRMRegression;

class AlertUtilsRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return 'BR-10152: Escaping user input in wrong place';
    }

    public function run(): void
    {
        $this->get('modules/Calendar/clients/base/views/scheduler/scheduler.js')
            ->expectRegexp('~<div class="calendarEventBody">.*?DOMPurify.sanitize\(htmlContent\).*?</div>~is');
    }
}
