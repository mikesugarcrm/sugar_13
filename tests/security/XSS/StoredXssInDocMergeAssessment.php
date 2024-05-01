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
use Regression\Severity;
use Regression\SugarCRMAssessment;

class StoredXssInDocMergeAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::MEDIUM;
    }

    public function getAssessmentDescription(): string
    {
        return 'Stored XSS  via "Doc Merge" template';
    }

    public function run(): void
    {
        $scenario = $this
            ->login('admin', 'asdf')
            ->apiCall('/metadata?type_filter=currencies%2Cfull_module_list%2Cmodules_info%2Chidden_subpanels%2Cjssource%2Cjssource_public%2Cordered_labels%2Cmodule_tab_map%2Cmodules%2Crelationships%2Cserver_info%2Cconfig%2C_override_values%2Cfilters%2Clogo_url%2Clogo_url_dark%2Ceditable_dropdown_filters&platform=base&module_dependencies=1')
            ->extract('jsSource', function (ResponseInterface $response) {
                $body = json_decode($response->getBody()->getContents(), true);

                return $body['jssource'];
            });

        $jsSource = $scenario->getVar('jsSource');

        $request = new Request('GET', $jsSource);

        $scenario
            ->send($request)
            ->assumeRegexp("/if\s*\(this\.name\s*===\s*'description'\)\s*\{\s*value\s*=\s*DOMPurify.sanitize\(value\)/", $escaped)
            ->checkAssumptions('Doc Merge notification\'s description isn\'t escaped.', !$escaped);
    }
}
