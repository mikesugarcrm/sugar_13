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


class SugarTestCaseUtilities
{
    private static $createdCases = [];

    private function __construct()
    {
    }

    /**
     * Create and save a new aCase bean.
     *
     * @param string $id ID of the record, defaults to ''.
     * @param array $caseValues Key-value mapping of values to preassign.
     * @return aCase The created case.
     */
    public static function createCase($id = '', $caseValues = [])
    {
        $time = random_int(0, mt_getrandmax());
        $case = new aCase();

        if (!isset($caseValues['name'])) {
            $case->name = 'SugarCase' . $time;
        }

        foreach ($caseValues as $property => $value) {
            $case->$property = $value;
        }

        if (!empty($id)) {
            $case->new_with_id = true;
            $case->id = $id;
        }
        $case->save();
        $GLOBALS['db']->commit();
        self::$createdCases[] = $case;
        return $case;
    }

    public static function setCreatedCase($case_ids)
    {
        foreach ($case_ids as $case_id) {
            $case = new aCase();
            $case->id = $case_id;
            self::$createdCases[] = $case;
        } // foreach
    } // fn

    /**
     * Hard-delete all cases created with createCase.
     */
    public static function removeAllCreatedCases()
    {
        $case_ids = self::getCreatedCaseIds();
        $GLOBALS['db']->query('DELETE FROM cases WHERE id IN (\'' . implode("', '", $case_ids) . '\')');
    }

    public static function getCreatedCaseIds()
    {
        $case_ids = [];
        foreach (self::$createdCases as $case) {
            $case_ids[] = $case->id;
        }
        return $case_ids;
    }
}
