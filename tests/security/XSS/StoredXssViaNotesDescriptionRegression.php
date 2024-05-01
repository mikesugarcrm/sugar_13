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
use Psr\Http\Message\ResponseInterface;
use Regression\SugarCRMRegression;

class StoredXssViaNotesDescriptionRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return "[BR-10449]: Stored XSS via notes description allows to attack all the user's in the organization.";
    }

    public function run(): void
    {
        $request = new Request('GET', 'clients/base/views/activity-card-content/activity-card-content.js');

        $this
            ->send($request)
            ->expectStatusCode(200)
            ->expectSubstring('_.escape(text).replace');
    }
}
