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
use Sugarcrm\Sugarcrm\Util\Uuid;

/**
 * @coversDefaultClass UploadFile
 */
class UploadFileTest extends TestCase
{
    private $db;
    private static $uploadStream;

    public static function setUpBeforeClass(): void
    {
        $customUploadStreamClass = new class extends UploadStream {
            public static function getDir()
            {
                if (empty(self::$upload_dir)) {
                    if (empty(self::$upload_dir)) {
                        self::$upload_dir = 'upload/custom';
                    }
                    if (!file_exists(self::$upload_dir)) {
                        sugar_mkdir(self::$upload_dir, 0755, true);
                    }
                }
                return self::$upload_dir;
            }

            public function getFSPath($path)
            {
                $path = substr($path, strlen(self::STREAM_NAME) + 3); // cut off upload://
                $path = str_replace('\\', '/', $path); // canonicalize path
                if ($path == '..' || substr($path, 0, 3) == '../' || substr($path, -3, 3) == '/..' || strstr($path, '/../')) {
                    $GLOBALS['log']->fatal("Invalid uploaded file name supplied: $path");
                    return null;
                }

                // split to directories only guid-named files
                if (is_guid($path)) {
                    // lower digits of timestamp in UUID-v1 should have good enough distribution
                    $path = substr($path, 5, 3) . '/' . $path;
                }

                return self::getDir() . '/' . $path;
            }
        };

        self::$uploadStream = new $customUploadStreamClass();
    }

    protected function setUp(): void
    {
        $this->db = SugarTestHelper::setUp('mock_db');
    }

    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        rmdir_recursive('upload/custom');
    }

    public function unlinkFileDataProvider()
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
     * @covers ::unlink_file
     * @dataProvider unlinkFileDataProvider
     */
    public function testUnlinkFile($rows, $expected)
    {
        $id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $file = "upload://{$id}";
        file_put_contents($file, $id);

        $this->db->addQuerySpy(
            'upload_id',
            "/SELECT upload_id FROM notes WHERE upload_id='{$id}'/",
            [$rows]
        );

        $actual = UploadFile::unlink_file($id);
        $this->assertSame($expected, file_exists($file), 'The filesystem is not correct');
        $this->assertSame(!$expected, $actual, 'The result of the function call is not correct');

        unlink($file);
    }

    /**
     * @covers ::unlink_file
     */
    public function testUnlinkFile_FileDoesNotExist()
    {
        $id = Sugarcrm\Sugarcrm\Util\Uuid::uuid1();

        $rows = [];
        $this->db->addQuerySpy(
            'upload_id',
            "/SELECT upload_id FROM notes WHERE upload_id='{$id}'/",
            [$rows]
        );

        $actual = UploadFile::unlink_file($id);
        $this->assertFalse($actual);
    }

    /**
     * Test for checking that users have no possibility to upload .htaccess file to upload folder
     * @covers ::final_move
     */
    public function testUploadHtaccess()
    {
        $uploadFile = new UploadFile('test');

        $this->expectExceptionObject(new \DomainException('Invalid Bean ID'));
        $uploadFile->final_move('.htaccess');
    }

    public function testDuplicateExistingDir(): void
    {
        $oldId = Uuid::uuid4();
        $source = self::$uploadStream->getFSPath("upload://$oldId");
        $newId = Uuid::uuid4();

        sugar_mkdir($source, 0777, true);

        $this->assertFalse(UploadFile::duplicate_file($oldId, $newId));
    }

    public function testDuplicateOldStyleExistingDir(): void
    {
        $oldId = Uuid::uuid4();
        $source = self::$uploadStream->getFSPath("upload://$oldId.ext");
        $newId = Uuid::uuid4();

        sugar_mkdir($source, 0777, true);

        $this->assertFalse(UploadFile::duplicate_file($oldId, $newId, '.ext'));
    }

    public function testDuplicateExistingFile(): void
    {
        $oldId = Uuid::uuid4();
        $source = self::$uploadStream->getFSPath("upload://$oldId");
        $newId = Uuid::uuid4();
        sugar_touch($source);

        $this->assertTrue(UploadFile::duplicate_file($oldId, $newId));
    }

    public function testDuplicateOldStyleExistingFile(): void
    {
        $oldId = Uuid::uuid4();
        $source = self::$uploadStream->getFSPath("upload://$oldId");
        $newId = Uuid::uuid4();
        sugar_touch($source);

        $this->assertTrue(UploadFile::duplicate_file($oldId, $newId, '.ext'));
    }
}
