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

/**
 * Test upload image with chart to server cache directory
 *
 * @ticket BR-8051
 * @ticket BR-8102
 */
class UploadImageActionTest extends TestCase
{
    private $uploaded_files = [];

    protected function setUp(): void
    {
        /* Mock getUserPrivGuid function for User instance */
        $user_priv_guid = create_guid();
        $user = $this
            ->getMockBuilder('Users')
            ->setMethods(['getUserPrivGuid'])
            ->getMock();

        $user->expects($this->any())
            ->method('getUserPrivGuid')
            ->willReturn($user_priv_guid);

        $GLOBALS['current_user'] = $user;
    }

    protected function tearDown(): void
    {
        /* delete uploaded files */
        foreach ($this->uploaded_files as $file_name) {
            unlink($file_name);
        }

        SugarTestHelper::tearDown();
    }

    /**
     * Provider for ::testSaveImage
     * @return array
     */
    public function SaveImageDataProvider(): array
    {
        return [
            [
                'chart.png',
                create_guid(),
            ],
        ];
    }

    /**
     * test Save image to cache
     * @dataProvider SaveImageDataProvider
     */
    public function testSaveImage($image_filename, $chart_id): void
    {

        $sugar_config = [];
        $file_name = null;
        $filepath = null;
        /* encode image to send via http post query */
        $full_image_filename = __DIR__ . '/' . $image_filename;
        $file_extension = get_file_extension($full_image_filename);
        $allowed_mime_types = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        $mime_type = $allowed_mime_types[$file_extension];
        $image_binary_data = file_get_contents($full_image_filename);
        $image_encoded = str_replace('+', ' ', base64_encode($image_binary_data));
        $image_str = 'data:' . $mime_type . ';base64' . ',' . $image_encoded;

        /* create params for http query */
        $_GET['DynamicAction'] = 'saveImage';
        $_POST['chart_id'] = $chart_id;
        $_POST['imageStr'] = $image_str;

        $sugar_config['upload_maxsize'] = 1000000;

        /* execute saveImage action */
        require 'modules/Charts/DynamicAction.php';

        /* check if file has been uploaded to path cache/images */
        $priv_guid = $GLOBALS['current_user']->getUserPrivGuid();
        $uploaded_file_name = $priv_guid . '_' . $chart_id . '_saved_chart.' . $file_extension;
        $uploaded_file_path = sugar_cached('images/' . $file_name);

        $this->assertTrue(file_exists($filepath), "File $uploaded_file_path not found");

        $this->uploaded_files[] = $uploaded_file_path;
    }
}
