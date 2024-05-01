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
/**
 * This array define the default value to use for the sugarpdf settings.
 * The order is DB (user or system) -> custom sugarpdf_default -> OOB sugarpdf_default
 */
$sugarpdf_default = [
    'K_PATH_MAIN' => 'include/tcpdf/',
    'K_PATH_URL' => 'customized/include/tcpdf/',
    'K_PATH_FONTS' => 'include/tcpdf/fonts/',
    'K_PATH_CUSTOM_FONTS' => 'custom/include/tcpdf/fonts/',
    'K_PATH_CACHE' => sugar_cached('include/tcpdf/'),
    'K_PATH_URL_CACHE' => sugar_cached('include/tcpdf/'),
    'K_PATH_CUSTOM_IMAGES' => 'custom/themes/default/images/',
    'K_PATH_IMAGES' => 'themes/default/images/',
    'K_BLANK_IMAGE' => 'themes/default/images/_blank.png',
    'PDF_PAGE_FORMAT' => 'LETTER',
];
