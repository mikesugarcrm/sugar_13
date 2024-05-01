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

//Helper functions used by both SOAP and REST Unit Test Calls.

class APIv3Helper
{
    public function populateSeedDataForSearchTest($user_id)
    {
        $results = [];
        $a1_id = create_guid();
        $a1 = new Account();
        $a1->id = $a1_id;
        $a1->new_with_id = true;
        $a1->name = "UNIT TEST $a1_id";
        $a1->assigned_user_id = $user_id;
        $a1->save();
        $results[] = ['id' => $a1_id, 'fieldName' => 'name', 'fieldValue' => "UNIT TEST $a1_id"];

        $a2_id = create_guid();
        $a2 = new Account();
        $a2->new_with_id = true;
        $a2->id = $a2_id;
        $a2->name = "UNIT TEST $a2_id";
        $a2->assigned_user_id = 'unittest';
        $a2->save();
        $results[] = ['id' => $a2_id, 'fieldName' => 'name', 'fieldValue' => "UNIT TEST $a2_id"];

        $c1_id = create_guid();
        $c1 = new Contact();
        $c1->id = $c1_id;
        $c1->new_with_id = true;
        $c1->first_name = 'UNIT TEST';
        $c1->last_name = 'UNIT_TEST';
        $c1->assigned_user_id = $user_id;
        $c1->save();
        $results[] = ['id' => $c1_id, 'fieldName' => 'name', 'fieldValue' => $c1->first_name . ' ' . $c1->last_name];

        $op1_id = create_guid();
        $op1 = new Opportunity();
        $op1->new_with_id = true;
        $op1->id = $op1_id;
        $op1->name = "UNIT TEST $op1_id";
        $op1->assigned_user_id = $user_id;
        $op1->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
        $op1->save();
        $results[] = ['id' => $op1_id, 'fieldName' => 'name', 'fieldValue' => "UNIT TEST $op1_id"];

        $op2_id = create_guid();
        $op2 = new Opportunity();
        $op2->new_with_id = true;
        $op2->id = $op2_id;
        $op2->name = "UNIT TEST $op2_id";
        $op2->assigned_user_id = 'unittest';
        $op2->date_closed = TimeDate::getInstance()->getNow()->asDbDate();
        $op2->save();
        $results[] = ['id' => $op2_id, 'fieldName' => 'name', 'fieldValue' => "UNIT TEST $op2_id"];
        $GLOBALS['db']->commit();
        return $results;
    }

    /**
     * Linear search function used to find a bean id in an entry list array.
     *
     * @param array $list
     * @param string $bean_id
     */
    public function findBeanIdFromEntryList($list, $bean_id, $module)
    {
        $found = false;
        foreach ($list as $moduleEntry) {
            if (is_object($moduleEntry)) {
                $moduleEntry = get_object_vars($moduleEntry);
            }
            if ($moduleEntry['name'] == $module) {
                foreach ($moduleEntry['records'] as $entry) {
                    if (is_object($entry)) {
                        $entry = get_object_vars($entry);
                    }
                    foreach ($entry as $fieldEntry) {
                        if (is_object($fieldEntry)) {
                            $fieldEntry = get_object_vars($fieldEntry);
                        }
                        if ($fieldEntry['name'] == 'id' && $fieldEntry['value'] == $bean_id) {
                            return true;
                        }
                    }
                }
            }
        }

        return $found;
    }

    /**
     * Linear search function used to find a particular field in an entry list array.
     *
     * @param array $list
     * @param string $bean_id
     */
    public function findFieldByNameFromEntryList($list, $bean_id, $module, $fieldName)
    {
        $found = false;

        foreach ($list as $moduleEntry) {
            if (is_object($moduleEntry)) {
                $moduleEntry = get_object_vars($moduleEntry);
            }
            if ($moduleEntry['name'] == $module) {
                foreach ($moduleEntry['records'] as $entry) {
                    if (is_object($entry)) {
                        $entry = get_object_vars($entry);
                    }
                    $value = $this->retrieveFieldValueByFieldName($entry, $fieldName, $bean_id);
                    if ($value !== false) {
                        return $value;
                    }
                }
            }
        }

        return $found;
    }

    private function retrieveFieldValueByFieldName($entry, $fieldName, $beanId)
    {
        $found = false;
        $fieldValue = false;
        foreach ($entry as $fieldEntry) {
            if (is_object($fieldEntry)) {
                $fieldEntry = get_object_vars($fieldEntry);
            }
            if ($fieldEntry['name'] == 'id' && $fieldEntry['value'] == $beanId) {
                $found = true;
            }

            if ($fieldEntry['name'] == $fieldName) {
                $fieldValue = $fieldEntry['value'];
            }
        }

        if ($found) {
            return $fieldValue;
        } else {
            return false;
        }
    }
}
