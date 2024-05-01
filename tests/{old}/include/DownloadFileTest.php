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

require_once 'include/download_file.php';

/**
 * Test for DownloadFile class.
 *
 * Class DownloadFileTest
 */
class DownloadFileTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @param array $data
     * @param array $expected
     * @covers       DownloadFile::getFileNamesForArchive
     * @dataProvider getData
     */
    public function testGetFileNamesForArchive($data, $expected)
    {
        $map = [];
        $beans = [];
        foreach ($data as $info) {
            $bean = BeanFactory::newBean('Accounts');
            $bean->name = $info['name'];
            array_push($beans, $bean);
            array_push($map, [$bean, 'somefield', $info]);
        }

        $df = $this->createPartialMock('DownloadFile', ['validateBeanAndField', 'getFileInfo']);
        $df->expects($this->any())
            ->method('validateBeanAndField')
            ->willReturn(true);
        $df->expects($this->any())
            ->method('getFileInfo')
            ->will($this->returnValueMap($map));

        $result = $df->getFileNamesForArchive($beans, 'somefield');
        $this->assertEquals($expected, $result);
    }

    /**
     * Data Provider for test.
     * @return array
     */
    public function getData()
    {
        return [
            [
                [
                    [
                        'name' => 'file1',
                        'path' => 'path1',
                    ],
                    [
                        'name' => 'file1',
                        'path' => 'path2',
                    ],
                    [
                        'name' => 'file2.jpg',
                        'path' => 'path3',
                    ],
                    [
                        'name' => 'file2.jpg',
                        'path' => 'path4',
                    ],
                    [
                        'name' => 'file3.jpg',
                        'path' => 'path5',
                    ],
                    [
                        'name' => 'file4',
                        'path' => 'path6',
                    ],
                ],
                [
                    'file1_0' => 'path1',
                    'file1_1' => 'path2',
                    'file2_0.jpg' => 'path3',
                    'file2_1.jpg' => 'path4',
                    'file3.jpg' => 'path5',
                    'file4' => 'path6',
                ],
            ],
        ];
    }

    /**
     * @covers       DownloadFile::cleanFilename
     * @dataProvider providerTestCleanFilename
     * @param string $filename the filename to test
     * @param string $expected the expected resulting filename
     */
    public function testCleanFilename($filename, $expected)
    {
        $downloadFile = $this->getMockBuilder('DownloadFile')
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $result = SugarTestReflection::callProtectedMethod($downloadFile, 'cleanFilename', [$filename]);
        $this->assertEquals($expected, $result);
    }

    public function providerTestCleanFilename()
    {
        return [
            ['', ''],
            ['fakefile.txt', 'fakefile.txt'],
            ["fake\nfile.txt", 'fakefile.txt'],
            ["fake\rfile.txt", 'fakefile.txt'],
            ["fake\r\nfile.txt", 'fakefile.txt'],
            ['fake file.txt', 'fake file.txt'],
        ];
    }
}
