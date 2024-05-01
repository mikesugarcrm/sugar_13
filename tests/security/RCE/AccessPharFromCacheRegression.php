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
use Regression\SugarCRMScenario;

class AccessPharFromCacheRegression extends SugarCRMScenario
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10139] Create security regression test for SugarCRM 0-day RCE';
    }

    public function run(): void
    {
        $pharRequest = new Request('GET', 'cache/images/test.phar');

        $this
            ->send($pharRequest)
            ->expectStatusCode(404)
            ->expectSubstring('The requested document was not found or you do not have sufficient permissions to view it');
    }
}
