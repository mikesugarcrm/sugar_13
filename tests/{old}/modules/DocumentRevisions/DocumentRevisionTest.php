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

use Sugarcrm\Sugarcrm\Util\Uuid;
use PHPUnit\Framework\TestCase;

class DocumentRevisionTest extends TestCase
{
    private static $docs = [];
    private static $files = [];

    public static function tearDownAfterClass(): void
    {
        foreach (static::$docs as $doc) {
            $doc->mark_deleted($doc->id);
        }

        foreach (static::$files as $file) {
            unlink($file);
        }
    }

    public function testSave_FileSizeIsSaved()
    {
        $filename = Uuid::uuid1();
        $file = "upload://{$filename}";
        file_put_contents($file, $filename);
        $filesize = filesize($file);

        $doc = BeanFactory::newBean('DocumentRevisions');
        $doc->id = $filename;
        $doc->new_with_id = true;
        $doc->save(false);
        static::$docs[] = $doc;

        $this->assertSame($filesize, $doc->file_size);
    }

    public function testSave_FileSizeIsZero()
    {
        $doc = BeanFactory::newBean('DocumentRevisions');
        $doc->id = Uuid::uuid1();
        $doc->new_with_id = true;
        $doc->save(false);
        static::$docs[] = $doc;

        $this->assertSame(0, $doc->file_size);
    }

    public function markDeletedProvider()
    {
        return [
            [
                [
                    'upload_id' => '123',
                ],
                true,
            ],
            [
                [],
                false,
            ],
        ];
    }

    /**
     * @covers ::mark_deleted
     * @dataProvider markDeletedProvider
     */
    public function testMarkDeleted($rows, $expected)
    {
        $doc = BeanFactory::newBean('DocumentRevisions');
        $doc->save(false);
        static::$docs[] = $doc;

        $file = "upload://{$doc->id}";
        file_put_contents($file, $doc->id);
        $this->assertFileExists($file);

        $db = SugarTestHelper::setUp('mock_db');
        $db->addQuerySpy(
            'upload_id',
            "/SELECT upload_id FROM notes WHERE upload_id='{$doc->id}' LIMIT 0,1/",
            [$rows]
        );

        $doc->mark_deleted($doc->id);
        $this->assertSame($expected, file_exists($file));

        unlink($file);
    }
}
