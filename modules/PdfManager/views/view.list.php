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


class PdfManagerViewList extends ViewList
{
    public function preDisplay()
    {
        parent::preDisplay();
        $this->lv->quickViewLinks = false;
        $this->lv->export = false;
        $this->lv->mergeduplicates = 0;
        $this->lv->showMassupdateFields = false;
    }
}
