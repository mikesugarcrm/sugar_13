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
require 'include/modules.php';
foreach ($beanFiles as $Classname => $filename) {
    // Find the name of the file generated by the updateclass script
    $pos = strrpos($filename, '/');
    $Newfilename = substr_replace($filename, 'SugarCore.', $pos + 1, 0);
    //delete the new SugarBean that extends CoreBean and replace it by the old one undoing all the changes
    if (file_exists($Newfilename)) {
        unlink($filename);
        $handle = file_get_contents($Newfilename);
        $data = preg_replace('/class SugarCore' . $Classname . '/', 'class ' . $Classname, $handle);
        $data1 = preg_replace('/function SugarCore' . $Classname . '/', 'function ' . $Classname, $data);
        file_put_contents($Newfilename, $data1);
        rename($Newfilename, $filename);
    }
}
