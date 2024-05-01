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


class SugarTestImportUtilities
{
    private static $createdFiles = [];

    private function __construct()
    {
    }

    public function __destruct()
    {
        self::removeAllCreatedFiles();
    }

    public static function createFile($lines = 2000, $columns = 3)
    {
        $filename = ImportCacheFiles::getImportDir() . '/test' . uniqid();
        $fp = fopen($filename, 'w');
        for ($i = 0; $i < $lines; $i++) {
            $line = [];
            for ($j = 0; $j < $columns; $j++) {
                $line[] = "foo{$i}{$j}";
            }
            fputcsv($fp, $line);
        }
        fclose($fp);

        self::$createdFiles[] = $filename;

        return $filename;
    }

    public static function createFileWithEOL(
        $lines = 2000,
        $columns = 3
    ) {

        $filename = ImportCacheFiles::getImportDir() . '/test' . date('YmdHis');
        $fp = fopen($filename, 'w');
        for ($i = 0; $i < $lines; $i++) {
            $line = [];
            for ($j = 0; $j < $columns; $j++) {
                // test both end of lines: \r\n (windows) and \n (unix)
                $line[] = "start{$i}\r\n{$j}\nend";
            }
            fputcsv($fp, $line);
        }
        fclose($fp);

        self::$createdFiles[] = $filename;

        return $filename;
    }

    public static function createFileWithWhiteSpace()
    {
        $filename = ImportCacheFiles::getImportDir() . 'testWhiteSpace' . date('YmdHis');
        $contents = <<<EOTEXT
account2,foo bar
EOTEXT;
        file_put_contents($filename, $contents);

        self::$createdFiles[] = $filename;

        return $filename;
    }

    public static function removeAllCreatedFiles()
    {
        foreach (self::$createdFiles as $file) {
            @unlink($file);
            $i = 0;
            while (true) {
                if (is_file($file . '-' . $i)) {
                    unlink($file . '-' . $i++);
                } else {
                    break;
                }
            }
        }
        ImportCacheFiles::clearCacheFiles();
    }
}
