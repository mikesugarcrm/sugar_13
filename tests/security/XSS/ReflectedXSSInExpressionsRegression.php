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

class ReflectedXSSInExpressionsRegression extends SugarCRMRegression
{
    public function getRegressionDescription(): string
    {
        return '[BR-11338]: Reflected XSS via href_object parameter';
    }

    public function run(): void
    {
        $this
            ->loginAs('admin')
            ->bwcLogin()
            ->get('index.php?module=Expressions&action=SingleSelector&sugar_body_only=true&type=dom_array&value=Self&opener_id=relate_type_user5&href_object=%27,alert(localStorage.getItem(%27prod:SugarCRM:AuthAccessToken%27)),%27')
            ->expectStatusCode(200)
            ->expectSubstring("set_return('relate_type_user5', '\\x26\\x23039\\x3B,alert\\x28localStorage.getItem\\x28\\x26\\x23039\\x3Bprod\\x3ASugarCRM\\x3AAuthAccessToken\\x26\\x23039\\x3B\\x29\\x29,\\x26\\x23039\\x3B')");
    }
}
