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

use GuzzleHttp\Psr7\Request;
use Regression\SugarCRMRegression;

class StoredXssInCalendarNameRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return "[BR-11174]: Stored XSS via Calendar Name allows to attack all the user's in the organization.";
    }

    public function run(): void
    {
        $request = new Request(
            'GET',
            'modules/Calendar/clients/base/views/scheduler/scheduler.js',
        );

        $this
            ->send($request)
            ->expectStatusCode(200)
            ->expectSubstring('text: DOMPurify.sanitize(calendarDef.name)')
            ->expectSubstring('value: DOMPurify.sanitize(calendarDef.id)')
            ->expectSubstring('color: DOMPurify.sanitize(calendarDef.color)');
    }
}
