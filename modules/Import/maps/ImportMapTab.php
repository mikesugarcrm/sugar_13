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

/*********************************************************************************
 * Description: Holds import setting for TSV (Tab Delimited) files
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 ********************************************************************************/
class ImportMapTab extends ImportMapOther
{
    /**
     * String identifier for this import
     */
    public $name = 'tab';
    /**
     * Field delimiter
     */
    public $delimiter = "\t";
    /**
     * Field enclosure
     */
    public $enclosure;
}