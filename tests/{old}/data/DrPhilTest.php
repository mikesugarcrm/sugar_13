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

use PHPUnit\Framework\TestCase;

/*
 * What is the DrPhilTest?
 * It's a test that runs through the metadata of the system and
 * verifies that the metadata is correct.
 * It was named DrPhilTest becase while it may find problems in your relationships
 * it does not attempt to fix them.
 *
 * If this test fails you are on the honor system to view this image: http://i.imgur.com/fMpZ4Rb.jpg
 */

class DrPhilTest extends TestCase
{
    /**
     * Relate fields which would fail the check for producing duplicates in SugarQuery
     */
    private const DUPLICATE_RELATE_FIELDS = [
        'Calls' => [
            'contact_name',
            'contact_id',
            'lead_name',
            'lead_id',
        ],
        'Contacts' => [
            'opportunity_role_fields',
            'c_accept_status_fields',
            'm_accept_status_fields',
        ],
        'DataSets' => [
            'child_name',
        ],
        'Documents' => [
            'related_doc_name',
            'related_doc_rev_number',
        ],
        'Employees' => [
            'c_accept_status_fields',
            'm_accept_status_fields',
        ],
        'Groups' => [
            'c_accept_status_fields',
            'm_accept_status_fields',
        ],
        'Leads' => [
            'c_accept_status_fields',
            'm_accept_status_fields',
        ],
        'Meetings' => [
            'contact_name',
            'contact_id',
        ],
        'Quotes' => [
            'opportunity_name',
        ],
        'Users' => [
            'c_accept_status_fields',
            'm_accept_status_fields',
        ],
        'Messages' => [
            'invitee_contact_name',
            'invitee_contact_id',
            'invitee_lead_name',
            'invitee_lead_id',
        ],
    ];

    /**
     * Link fields that would fail the check that there is only one link field per relationship side.
     * This is a temporary list which contains references to existing metadata and will be removed in the future.
     * Developers MUST NOT add new items to the list. If the newly added metadata fails the test,
     * the metadata should be fixed instead.
     */
    private const LINKS_ON_SAME_REL_SIDE_EXCEPTIONS = [
        'Leads' => [
            'contact_leads' => ['contacts', 'contact'],
        ],
        'ProjectTask' => [
            'projects_project_tasks' => ['projects', 'project_name_link'],
        ],
        'Contacts' => [
            'contact_direct_reports' => ['reports_to_link', 'direct_reports'],
            'contact_tasks' => ['tasks', 'all_tasks'],
        ],
        'Accounts' => [
            'member_accounts' => ['members', 'member_of'],
        ],
        'Tasks' => [
            'projects_tasks' => ['projects', 'project'],
        ],
        'ProductCategories' => [
            'member_categories' => ['parent_category', 'categories'],
        ],
        'Notes' => [
            'notes_attachments' => ['attachments', 'note_attachment'],
        ],
        'Tags' => [
            'hintaccountsets_tags' => ['hintaccountsets_link', 'hint_accountsets_link'],
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
    }

    public static function tearDownAfterClass(): void
    {
        SugarTestHelper::tearDown();
    }

    protected static function getValidModules()
    {
        static $validModules;

        if (!isset($validModules)) {
            SugarTestHelper::setUp('beanList');
            SugarTestHelper::setUp('app_list_strings');

            $validModules = [];

            $invalidModules = [
                'DynamicFields',
                'Connectors',
                'CustomFields',
                'Empty',
                'Audit',
                'Relationships',
            ];


            foreach (array_keys($GLOBALS['beanList']) as $moduleName) {
                if (in_array($moduleName, $invalidModules)) {
                    continue;
                }
                $validModules[] = $moduleName;
            }
        }

        return $validModules;
    }

    /**
     * @return SugarBean
     */
    protected static function getSeedBean($moduleName)
    {
        static $seedBeans = [];

        if (!isset($seedBeans[$moduleName])) {
            $seedBeans[$moduleName] = BeanFactory::newBean($moduleName);
        }

        return $seedBeans[$moduleName];
    }

    public static function provideValidModules()
    {
        static $validModulesDataSet;

        if (!isset($validModulesDataSet)) {
            $validModulesDataSet = [];

            $validModules = self::getValidModules();
            foreach ($validModules as $module) {
                $validModulesDataSet[] = [$module];
            }
        }

        return $validModulesDataSet;
    }

    /**
     * @group SanityCheck
     * @dataProvider provideValidModules
     */
    public function testCanLoadModules($moduleName)
    {
        $bean = BeanFactory::newBean($moduleName);
        $this->assertNotNull($bean, "Could not load bean: $moduleName");
    }

    /**
     * @group SanityCheck
     * @dataProvider provideValidModules
     */
    public function testFieldDefs($moduleName)
    {
        $bean = self::getSeedBean($moduleName);
        $this->assertTrue(is_array($bean->field_defs), "No field defs for {$bean->module_dir}");

        foreach ($bean->field_defs as $key => $def) {
            $this->checkFieldDefinition($bean->module_dir, $bean->field_defs, $key, $def, $bean::$relateFieldTypes);
        }

        //Check correct definitions for fields in primary keys.
        foreach ($this->getBeanPrimaryIndexes($bean) as $index) {
            $this->assertIsArray($index['fields'], 'Fields for primary index should be as array');
            foreach ($index['fields'] as $field) {
                $def = $bean->getFieldDefinition($field);
                $this->assertNotEmpty($def, 'Field for primary key should exists');
                $bean->db->massageFieldDef($def);
                $this->assertFalse(
                    SugarTestReflection::callProtectedMethod($bean->db, 'isNullable', [$def]),
                    'Field for primary key shouldn\'t be nullable'
                );
            }
        }

        foreach ($bean->getIndices() as $key => $definition) {
            $this->checkIndexDefinition($definition, $moduleName, $key);
        }
    }

    /**
     * Test definitions in metadata
     *
     * @dataProvider tableMetadataProvider
     */
    public function testMetaDefs($table, $metadata)
    {
        $db = DBManagerFactory::getInstance();

        foreach ($metadata['fields'] as $key => $def) {
            $this->checkFieldDefinition($table, $metadata['fields'], $key, $def, []);
        }

        foreach ($this->getMetaPrimaryIndexes($metadata) as $index) {
            $this->assertIsArray($index['fields'], 'Fields for primary index should be as array');
            foreach ($index['fields'] as $field) {
                $def = $metadata['fields'][$field];
                $this->assertNotEmpty($def, 'Field for primary key should exists');
                $db->massageFieldDef($def);
                $this->assertFalse(
                    SugarTestReflection::callProtectedMethod($db, 'isNullable', [$def]),
                    'Field for primary key shouldn\'t be nullable'
                );
            }
        }

        if (isset($metadata['indices'])) {
            foreach ($metadata['indices'] as $key => $definition) {
                $this->checkIndexDefinition($definition, $table, $key);
            }
        }
    }

    /**
     * Checks whether the field is properly defined
     *
     * @param string $table Table or module name
     * @param array $defs All field definitions
     * @param string $key Definition key
     * @param array $def Definition
     * @param array $relate_types Allowed types of relate fields
     */
    private function checkFieldDefinition($table, array $defs, $key, array $def, array $relate_types)
    {
        $this->assertArrayHasKey('name', $def, "Def for $table/$key is missing a name attribute");
        $this->assertEquals($key, $def['name'], "Def's name for $table/$key doesn't match the key");
        $this->assertArrayHasKey('type', $def, "Def for $table/$key is missing a type");

        // Teams operate in their own weird way
        if ($key == 'team_name') {
            return;
        }

        if (in_array($def['type'], $relate_types)
            || (isset($def['source']) && $def['source'] == 'non-db' && !empty($def['link']))) {
            // These are related items, they get checked differently
            return;
        }

        if (!empty($def['rname'])
            && $def['type'] != 'link'
            && !empty($def['source'])
            && $def['source'] == 'non-db') {
            $this->assertTrue(!empty($def['link']), "Def for $table/{$key} has an rname, but no link");
        }

        if (isset($def['sort_on'])) {
            // Sort on can be either a string or an array... make it an array
            // for testing
            if (is_string($def['sort_on'])) {
                $def['sort_on'] = [$def['sort_on']];
            }

            // Loop and test
            foreach ($def['sort_on'] as $sortField) {
                $this->assertArrayHasKey($sortField, $defs, "Sort on for $table/$key points to an invalid field.");
            }
        }

        // verify pii and audited fields
        if (isset($def['pii']) && isTruthy($def['pii'])) {
            $this->assertTrue(isset($def['audited']) && isTruthy($def['audited']), "$table:$key contains mismatch audited and pii value.");
        }

        if (isset($def['fields'])) {
            foreach ($def['fields'] as $subField) {
                $fieldName = is_array($subField) ? $subField['name'] : $subField;
                $this->assertArrayHasKey($key, $defs, "Sub field $fieldName for $table/$key points to an invalid field.");
            }
        }

        if (isset($def['db_concat_fields'])) {
            foreach ($def['db_concat_fields'] as $subField) {
                $this->assertArrayHasKey($subField, $defs, "DB concat field $subField for $table/$key points to an invalid field.");
            }
        }

        // Tag field verifications
        if ($key === 'tag') {
            $this->assertArrayHasKey('studio', $defs, 'Studio config should be defined for the tag field');
            $this->assertArrayHasKey('base', $defs['studio'], 'Base studio config should be defined for the tag field');
            $this->assertArrayHasKey('popupsearch', $defs['studio']['base'], 'Popup search view config should be defined for the tag field');
            $this->assertFalse($defs['studio']['base']['popupsearch'], 'Tags is not an allowed field on the BWC Popup Search view in studio');
        }

        if ($def['type'] === 'id') {
            $this->assertArrayNotHasKey('len', $def, sprintf(
                'The ID field %s.%s is not expected to have length defined',
                $table,
                $key
            ));
        }
    }

    private function checkIndexDefinition(array $definition, string $table, $key): void
    {
        $this->assertArrayHasKey(
            'name',
            $definition,
            sprintf('Definition for index #%s on %s is missing name', $key, $table)
        );
    }

    /**
     * @param array $definition Field definition
     * @dataProvider rNameLinkFieldDefinitionProvider
     */
    public function testRNameLinkFieldDefinition(array $definition)
    {
        $this->assertArrayHasKey('source', $definition);
        $this->assertEquals('non-db', $definition['source']);
        $this->assertArrayHasKey('link', $definition);
        $this->assertNotEmpty($definition['link']);
    }

    public static function rNameLinkFieldDefinitionProvider()
    {
        $data = [];

        foreach (self::getValidModules() as $module) {
            $bean = self::getSeedBean($module);

            foreach ($bean->getFieldDefinitions() as $field => $definition) {
                if (isset($definition['rname_link'])) {
                    $data[$module . '#' . $field] = [$definition];
                }
            }
        }

        return $data;
    }

    public static function tableMetadataProvider()
    {
        $dictionary = $data = [];
        include 'modules/TableDictionary.php';

        foreach ($dictionary as $table => $metadata) {
            $data[] = [$table, $metadata];
        }

        return $data;
    }

    /**
     * Get primary indexes definition from metadata.
     * @param array $meta
     * @return array
     */
    protected function getMetaPrimaryIndexes($meta)
    {
        $result = [];

        if (empty($meta['indices'])) {
            return $result;
        }

        foreach ($meta['indices'] as $index) {
            if (strtolower($index['type']) == 'primary') {
                array_push($result, $index);
            }
        }
        return $result;
    }

    /**
     * Return primary indexes for provided bean.
     * @param SugarBean $bean
     * @return array
     */
    protected function getBeanPrimaryIndexes($bean)
    {
        $result = [];
        foreach ($bean->getIndices() as $index) {
            if (strtolower($index['type']) == 'primary') {
                array_push($result, $index);
            }
        }
        return $result;
    }

    public static function provideLinkFields()
    {
        $moduleList = self::getValidModules();
        $linkFields = [];
        $deniedLinks = ['notification_targets', 'accountsets',];
        foreach ($moduleList as $module) {
            $bean = self::getSeedBean($module);
            if (!is_array($bean->field_defs)) {
                continue;
            }

            foreach ($bean->field_defs as $linkName => $def) {
                if ($def['type'] != 'link') {
                    continue;
                }
                if (in_array($linkName, $deniedLinks)) {
                    continue;
                }

                $linkFields[] = [$module, $linkName];
            }
        }

        return $linkFields;
    }

    /**
     * @group SanityCheck
     * @dataProvider provideLinkFields
     */
    public function testLinkFields($moduleName, $linkName)
    {
        $bean = self::getSeedBean($moduleName);

        $bean->load_relationship($linkName);
        $this->assertNotNull($bean->$linkName, "Could not load link {$bean->module_dir}/{$linkName}");

        $relatedModuleName = $bean->$linkName->getRelatedModuleName();
        $this->assertNotNull($relatedModuleName, "Could not figure out the related module name for link {$bean->module_dir}/{$linkName}");

        $relatedBean = self::getSeedBean($relatedModuleName);
        $this->assertNotNull($relatedBean, "Could not load related module ({$relatedModuleName}) for link {$bean->module_dir}/{$linkName}");

        $linkDef = $bean->field_defs[$linkName];
        $relationship = $linkDef['relationship'];
        foreach ($bean->field_defs as $key => $def) {
            if ($def['type'] !== 'link') {
                continue;
            }

            if ($key == $linkName) {
                continue;
            }

            if ($relationship !== $def['relationship']) {
                continue;
            }

            if (isset($linkDef['side']) && isset($def['side']) && $linkDef['side'] !== $def['side']) {
                continue;
            }

            $exceptions = self::LINKS_ON_SAME_REL_SIDE_EXCEPTIONS[$moduleName][$relationship] ?? [];
            if (in_array($linkName, $exceptions)) {
                continue;
            }

            $this->fail(
                'Only one link field per relationship side is allowed. '
                . "Both '$linkName' and '$key' links are referencing the same "
                . "relationship '$relationship'."
            );
        }

        return;

        // The following tests make sure that the relationship has both ends.
        // the world is too cruel for these tests right now.
        static $allowedOneWay = [
            'Users' => 'Users',
            'Activities' => 'Activities',
        ];

        if (isset($allowedOneWay[$relatedModuleName])) {
            return;
        }

        $relatedLinkName = $bean->$linkName->getRelatedModuleLinkName();
        $this->assertNotNull($relatedLinkName, "Could not load related module's link record for link {$bean->module_dir}/{$linkName}");

        $relatedBean->load_relationship($relatedLinkName);
        $this->assertNotNull($relatedBean->$relatedLinkName, "Could not load related module link {$relatedBean->module_dir}/{$relatedLinkName}");
    }

    /**
     * Test that moduleList and moduleListSingular are in sync
     */
    public function testModuleList()
    {
        $diff = array_diff(array_keys($GLOBALS['app_list_strings']['moduleList']), array_keys($GLOBALS['app_list_strings']['moduleListSingular']));
        $this->assertEquals([], $diff, 'Key lists do not match');
    }

    /**
     * @dataProvider relateFieldProvider
     */
    public function testRelateField($module, $field)
    {
        $bean = self::getSeedBean($module);
        $definition = $bean->getFieldDefinition($field);

        $this->assertThat($definition, $this->logicalOr(
            $this->arrayHasKey('link'),
            $this->logicalAnd(
                $this->arrayHasKey('module'),
                $this->arrayHasKey('id_name')
            )
        ));

        if (in_array($field, self::DUPLICATE_RELATE_FIELDS[$module] ?? [])) {
            return;
        }

        $query = new SugarQuery();
        $query->from($bean);
        $query->select($field);
        $duplicates = SugarTestReflection::callProtectedMethod($bean, 'queryProducesDuplicates', [$query]);
        $this->assertFalse($duplicates, 'Fetching related field should not produce duplicates');
    }

    public static function relateFieldProvider()
    {
        foreach (self::getValidModules() as $module) {
            $bean = self::getSeedBean($module);

            if (!isset($bean->field_defs)) {
                continue;
            }

            foreach ($bean->field_defs as $field => $vardef) {
                if (!isset($vardef['type']) || $vardef['type'] !== 'relate') {
                    continue;
                }

                yield sprintf('%s.%s', $module, $field) => [$module, $field];
            }
        }
    }

    protected function getMustNotOverridenFields()
    {
        //here is the list of fields in SugarBean that must not be overridden or redefined
        return [
            'db',
            'table_name',
            'object_name',
            'module_dir',
            'module_name',
            'field_defs',
            'custom_fields',
            'list_fields',
            'additional_column_fields',
            'relationship_fields',
            'fetched_row',
            'disable_custom_fields',
            'new_with_id',
            'disable_row_level_security',
            'visibility',
            'max_logic_depth',
            'disable_vardefs',
            'save_from_post',
            'duplicates_found',
            'update_date_modified',
            'update_modified_by',
            'update_date_entered',
            'importable',
            'in_workflow',
            'tracker_visibility',
            'loaded_relationships',
            'module_key',
            'name_format_map',

            'loadedDefs',
        ];
    }

    /**
     * @dataProvider overriddenSugarBeanFieldsProvider
     */
    public function testOverriddenSugarBeanFields($module)
    {
        $mustNotOverriddenFields = $this->getMustNotOverridenFields();

        $bean = self::getSeedBean($module);
        if (isset($bean->object_name)) {
            $objectName = $bean->object_name;
            if (isset($GLOBALS['dictionary'][$objectName])) {
                $vardefFields = $GLOBALS['dictionary'][$objectName]['fields'];
                $this->assertFalse(empty($vardefFields), "Empty list of fields for module {$module}");
                foreach ($vardefFields as $field => $value) {
                    if (!$this->isExistingException($module, $field)) {
                        $this->assertFalse(
                            in_array($field, $mustNotOverriddenFields),
                            "Field {$field} is overridden for module {$module}"
                        );
                    }
                }
            }
        }
    }

    public function isExistingException($module, $field)
    {
        // When this test is added, there are 3 known exceptions already as follows:
        // This test is to make sure that NO new exception would be added.
        // module: EditCustomFields field: importable
        // module: Trackers         field: module_name
        // module: Filters          field: module_name
        if (($module === 'EditCustomFields' && $field === 'importable') ||
            ($module === 'Trackers' && $field === 'module_name') ||
            ($module === 'Filters' && $field === 'module_name')) {
            return true;
        } else {
            return false;
        }
    }

    public static function overriddenSugarBeanFieldsProvider()
    {
        $modules = [];
        $validModules = self::getValidModules();
        foreach ($validModules as $module) {
            $modules[] = [$module];
        }
        return $modules;
    }

    /**
     * @dataProvider m2MRelationshipsProvider
     */
    public function testM2MRelationships($name, array $def)
    {
        $this->assertArrayHasKey('fields', $def, 'M2M relationship must have field definitions');

        foreach ($def['fields'] as $key => $fieldDef) {
            $this->checkFieldDefinition($name, $def['fields'], $key, $fieldDef, []);
        }
    }

    public static function m2MRelationshipsProvider()
    {
        $factory = SugarRelationshipFactory::getInstance();
        $factory->rebuildCache();
        $defs = $factory->getRelationshipDefs();
        $data = [];
        foreach ($defs as $name => $def) {
            if (isset($def['relationship_type']) && $def['relationship_type'] == 'many-to-many') {
                $data[$name] = [$name, $def];
            }
        }

        ksort($data);

        return $data;
    }

    /**
     * @test
     */
    public function tableDictionaryDoesNotHaveBeanDefinitions()
    {
        $dictionary = [];
        require 'modules/TableDictionary.php';

        $objects = array_map(function (string $module) {
            return BeanFactory::getObjectName($module);
        }, self::getValidModules());

        $this->assertEmpty(array_intersect(array_keys($dictionary), $objects));
    }
}
