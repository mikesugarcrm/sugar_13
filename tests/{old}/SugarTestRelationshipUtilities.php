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


class SugarTestRelationshipUtilities
{
    private static $relsAdded = [];

    private static $relRequiredKeys = [
        'relationship_type',
        'lhs_module',
        'rhs_module',
    ];

    /**
     * Create a relationship
     *
     * Params should be passed in as this:
     *
     * array(
     *       'relationship_type' => 'one-to-many',
     *       'lhs_module' => 'Accounts',
     *       'rhs_module' => 'Accounts',
     *   )
     *
     * @static
     * @param array $relationship_def
     * @return ActivitiesRelationship|bool|ManyToManyRelationship|ManyToOneRelationship|OneToManyRelationship|OneToOneRelationship
     */
    public static function createRelationship(array $relationship_def)
    {
        if (!self::checkRequiredFields($relationship_def)) {
            return false;
        }

        $relationships = new DeployedRelationships($relationship_def['lhs_module']);

        if (!isset($relationship_def['view_module'])) {
            $relationship_def['view_module'] = $relationship_def['lhs_module'];
        }

        $REQUEST_Backup = $_REQUEST;

        $_REQUEST = $relationship_def;

        $relationship = $relationships->addFromPost();
        $relationships->save();
        $relationships->build();
        LanguageManager::clearLanguageCache($relationship_def['lhs_module']);

        SugarRelationshipFactory::rebuildCache();
        // rebuild the dictionary to make sure that it has the new relationship in it
        SugarTestHelper::setUp('dictionary');
        // reset the link fields since we added one
        VardefManager::$linkFields = [];

        $_REQUEST = $REQUEST_Backup;
        unset($REQUEST_Backup);


        self::$relsAdded[] = $relationship->getDefinition();

        return $relationship;
    }

    /**
     * Remove all created relationships
     *
     * @static
     */
    public static function removeAllCreatedRelationships()
    {
        foreach (self::$relsAdded as $rel) {
            $relationships = new DeployedRelationships($rel['lhs_module']);

            $relationships->delete($rel['relationship_name']);

            $relationships->save();
            LanguageManager::clearLanguageCache($rel['lhs_module']);
            SugarRelationshipFactory::deleteCache();

            SugarRelationshipFactory::rebuildCache();
        }
        // since we are creating a relationship we need to unset this global var
        if (isset($GLOBALS['reload_vardefs'])) {
            unset($GLOBALS['reload_vardefs']);
        }
    }

    /**
     * Make sure we have at least the required keys
     *
     * @static
     * @param array $relationship_def
     * @return bool
     */
    protected static function checkRequiredFields(array $relationship_def)
    {
        foreach (self::$relRequiredKeys as $key) {
            if (!array_key_exists($key, $relationship_def)) {
                return false;
            }
        }

        return true;
    }
}
