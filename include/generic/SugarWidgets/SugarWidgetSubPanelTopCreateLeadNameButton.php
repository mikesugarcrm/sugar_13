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


class SugarWidgetSubPanelTopCreateLeadNameButton extends SugarWidgetSubPanelTopButtonQuickCreate
{
    public function display(array $defines, $additionalFormFields = [])
    {
        global $app_strings;
        global $currentModule;

        $title = $app_strings['LBL_NEW_BUTTON_TITLE'];
        //$accesskey = $app_strings['LBL_NEW_BUTTON_KEY'];
        $value = $app_strings['LBL_NEW_BUTTON_LABEL'];
        $this->module = 'Leads';

        /**
         * if module is hidden or subpanel for the module is hidden - doesn't show quick create button
         */
        if (SugarWidget::isModuleHidden($this->module)) {
            return '';
        }

        if (ACLController::moduleSupportsACL($defines['module']) && !ACLController::checkAccess($defines['module'], 'edit', true)) {
            $button = "<input title='$title'class='button' type='button' name='button' value='  $value  ' disabled/>\n";
            return $button;
        }

        $additionalFormFields = [];

        //from accounts
        if ($defines['focus']->object_name == 'Account') {
            if (isset($defines['focus']->billing_address_street)) {
                $additionalFormFields['primary_address_street'] = $defines['focus']->billing_address_street;
            }
            if (isset($defines['focus']->billing_address_city)) {
                $additionalFormFields['primary_address_city'] = $defines['focus']->billing_address_city;
            }
            if (isset($defines['focus']->billing_address_state)) {
                $additionalFormFields['primary_address_state'] = $defines['focus']->billing_address_state;
            }
            if (isset($defines['focus']->billing_address_country)) {
                $additionalFormFields['primary_address_country'] = $defines['focus']->billing_address_country;
            }
            if (isset($defines['focus']->billing_address_postalcode)) {
                $additionalFormFields['primary_address_postalcode'] = $defines['focus']->billing_address_postalcode;
            }
            if (isset($defines['focus']->phone_office)) {
                $additionalFormFields['phone_work'] = $defines['focus']->phone_office;
            }
            if (isset($defines['focus']->id)) {
                $additionalFormFields['account_id'] = $defines['focus']->id;
            }
        }
        //from contacts
        if ($defines['focus']->object_name == 'Contact') {
            if (isset($defines['focus']->salutation)) {
                $additionalFormFields['salutation'] = $defines['focus']->salutation;
            }
            if (isset($defines['focus']->first_name)) {
                $additionalFormFields['first_name'] = $defines['focus']->first_name;
            }
            if (isset($defines['focus']->last_name)) {
                $additionalFormFields['last_name'] = $defines['focus']->last_name;
            }
            if (isset($defines['focus']->primary_address_street)) {
                $additionalFormFields['primary_address_street'] = $defines['focus']->primary_address_street;
            }
            if (isset($defines['focus']->primary_address_city)) {
                $additionalFormFields['primary_address_city'] = $defines['focus']->primary_address_city;
            }
            if (isset($defines['focus']->primary_address_state)) {
                $additionalFormFields['primary_address_state'] = $defines['focus']->primary_address_state;
            }
            if (isset($defines['focus']->primary_address_country)) {
                $additionalFormFields['primary_address_country'] = $defines['focus']->primary_address_country;
            }
            if (isset($defines['focus']->primary_address_postalcode)) {
                $additionalFormFields['primary_address_postalcode'] = $defines['focus']->primary_address_postalcode;
            }
            if (isset($defines['focus']->phone_work)) {
                $additionalFormFields['phone_work'] = $defines['focus']->phone_work;
            }
            if (isset($defines['focus']->id)) {
                $additionalFormFields['contact_id'] = $defines['focus']->id;
            }
        }

        //from opportunities
        if ($defines['focus']->object_name == 'Opportunity') {
            if (isset($defines['focus']->id)) {
                $additionalFormFields['opportunity_id'] = $defines['focus']->id;
            }
            if (isset($defines['focus']->account_name)) {
                $additionalFormFields['account_name'] = $defines['focus']->account_name;
            }
            if (isset($defines['focus']->account_id)) {
                $additionalFormFields['account_id'] = $defines['focus']->account_id;
            }
        }

        $button = $this->_get_form($defines, $additionalFormFields);
        $button .= "<input title='$title' class='button' type='submit' name='{$this->getWidgetId()}_button' id='{$this->getWidgetId()}' value='  $value  '/>\n";
        $button .= '</form>';
        return $button;
    }
}
