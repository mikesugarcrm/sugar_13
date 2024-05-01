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

use Psr\Http\Message\ResponseInterface;
use Regression\SugarCRMAssessment;

class NotesAssessment extends SugarCRMAssessment
{
    public function run(): void
    {
        $this->login('sarah', 'sarah')
            ->bwcLogin()
            ->submitForm('index.php?module=Notes&action=save', [], 'index.php?module=Notes&action=index')
            ->assume(function (ResponseInterface $response) {
                $content = (string)$response->getBody();
                return (false === strpos($content, 'DEPRECATED')) && $response->getStatusCode() === 200;
            }, $result)
            ->checkAssumptions('Uploads in Notes module are not deprecated', $result);
    }

    public function getAssessmentDescription(): string
    {
        return 'Unrestricted File Upload through Notes module';
    }
}
