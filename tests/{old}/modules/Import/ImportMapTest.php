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

class ImportMapTest extends TestCase
{
    private $importMap;

    protected function setUp(): void
    {
        $beanList = [];
        $beanFiles = [];
        require 'include/modules.php';
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $this->importMap = new ImportMap();
        $this->importMap->enclosure = '"';
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['beanList']);
        unset($GLOBALS['beanFiles']);
        $GLOBALS['db']->query(
            'DELETE FROM import_maps 
                WHERE assigned_user_id IN (\'' .
            implode("','", SugarTestUserUtilities::getCreatedUserIds()) . '\')'
        );
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    private function addMapping(
        $name = 'test mapping for importmaptest',
        $enclosure = '"'
    ) {

        $this->importMap->save(
            $GLOBALS['current_user']->id,
            $name,
            'TEST',
            'other',
            '1',
            ',',
            $enclosure
        );
    }

    public function testSave()
    {
        $this->addMapping();
        $query = "SELECT * FROM import_maps 
                    WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                        AND name = 'test mapping'
                        AND module = 'TEST'
                        AND source = 'other'
                        AND has_header = '1'
                        AND delimiter = ','
                        AND enclosure = '\"'";

        $result = $GLOBALS['db']->query($query);

        $this->assertFalse($GLOBALS['db']->fetchByAssoc($result), 'Row not added');
    }

    public function testSaveEmptyEnclosure()
    {
        $this->addMapping('test mapping', '');
        $query = "SELECT * FROM import_maps 
                    WHERE assigned_user_id = '{$GLOBALS['current_user']->id}'
                        AND name = 'test mapping'
                        AND module = 'TEST'
                        AND source = 'other'
                        AND has_header = '1'
                        AND delimiter = ','
                        AND enclosure = ' '";

        $result = $GLOBALS['db']->query($query);

        $this->assertTrue($GLOBALS['db']->fetchByAssoc($result) != false, 'Row not added');
    }

    public function testSetAndGetMapping()
    {
        $mapping = [
            'field1' => 'value1',
            'field2' => 'value2',
            'D&B Principal Id' => 'db_principal_id',
        ];

        $this->importMap->setMapping($mapping);
        $enclosure = $this->importMap->enclosure;
        // Save to a DB with the same enclosure.
        $this->addMapping('Test mapping.', $enclosure);
        $id = $this->importMap->id;

        $importMapRetrieve = new ImportMap();
        $importMapRetrieve->retrieve($id, false);

        $this->assertEquals($importMapRetrieve->getMapping(), $mapping);
    }

    public function testSetAndGetDefaultFields()
    {
        $mapping = [
            'field1' => 'value1',
            'field2' => 'value2',
            'D&B Principal Id' => 'db_principal_id',
        ];

        $this->importMap->setDefaultValues($mapping);
        $enclosure = $this->importMap->enclosure;
        // Save to a DB with the same enclosure.
        $this->addMapping('Test mapping.', $enclosure);
        $id = $this->importMap->id;

        $importMapRetrieve = new ImportMap();
        $importMapRetrieve->retrieve($id, false);

        $this->assertEquals($importMapRetrieve->getDefaultValues(), $mapping);
    }

    public function testMarkPublished()
    {
        $this->addMapping();
        $this->assertTrue($this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        ));
        $id = $this->importMap->id;

        $query = "SELECT * FROM import_maps 
                    WHERE id = '$id'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['is_published'], 'yes');
    }

    public function testMarkPublishedNameConflict()
    {
        $this->addMapping();
        $this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        );

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->importMap = new ImportMap();
        $this->addMapping();
        $this->assertFalse($this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        ));

        $query = "SELECT * FROM import_maps 
                    WHERE id = '{$this->importMap->id}'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['is_published'], 'no');
    }

    public function testMarkPublishedNameNotAdmin()
    {
        $GLOBALS['current_user']->is_admin = '0';

        $this->addMapping();
        $this->assertFalse($this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        ));
    }

    public function testMarkUnpublished()
    {
        $this->addMapping();
        $this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        );
        $id = $this->importMap->id;

        $importMapRetrieve = new ImportMap();
        $importMapRetrieve->retrieve($id, false);
        $this->assertTrue($this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            false
        ));

        $query = "SELECT * FROM import_maps 
                    WHERE id = '$id'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['is_published'], 'no');
    }

    public function testMarkUnpublishedNameConflict()
    {
        $this->addMapping();
        $this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            true
        );
        $id = $this->importMap->id;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $this->importMap = new ImportMap();
        $this->addMapping();

        $importMapRetrieve = new ImportMap();
        $importMapRetrieve->retrieve($id, false);
        $this->assertFalse($this->importMap->mark_published(
            $GLOBALS['current_user']->id,
            false
        ));

        $query = "SELECT * FROM import_maps 
                    WHERE id = '$id'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['is_published'], 'yes');
    }

    public function testMarkDeleted()
    {
        $this->addMapping();
        $id = $this->importMap->id;

        $this->importMap = new ImportMap();
        $this->importMap->mark_deleted($id);

        $query = "SELECT * FROM import_maps 
                    WHERE id = '$id'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['deleted'], '1');
    }

    public function testMarkDeletedAdminDifferentUser()
    {
        $this->addMapping();
        $id = $this->importMap->id;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '1';
        $this->importMap = new ImportMap();
        $this->importMap->mark_deleted($id);

        $query = "SELECT * FROM import_maps 
                    WHERE id = '$id'";

        $result = $GLOBALS['db']->query($query);

        $row = $GLOBALS['db']->fetchByAssoc($result);

        $this->assertEquals($row['deleted'], '1');
    }

    public function testMarkDeletedNotAdminDifferentUser()
    {
        $this->addMapping();
        $id = $this->importMap->id;

        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->is_admin = '0';
        $this->importMap = new ImportMap();
        $this->assertFalse($this->importMap->mark_deleted($id), 'Record should not be allowed to be deleted');
    }

    public function testRetrieveAllByStringFields()
    {
        $this->addMapping();
        $this->importMap = new ImportMap();
        $this->addMapping('test mapping 2');
        $this->importMap = new ImportMap();
        $this->addMapping('test mapping 3');

        $objarr = $this->importMap->retrieve_all_by_string_fields(
            ['assigned_user_id' => $GLOBALS['current_user']->id]
        );

        $this->assertCount(3, $objarr);

        $this->assertEquals(
            $objarr[0]->assigned_user_id,
            $GLOBALS['current_user']->id
        );
        $this->assertEquals(
            $objarr[1]->assigned_user_id,
            $GLOBALS['current_user']->id
        );
        $this->assertEquals(
            $objarr[2]->assigned_user_id,
            $GLOBALS['current_user']->id
        );
    }
}
