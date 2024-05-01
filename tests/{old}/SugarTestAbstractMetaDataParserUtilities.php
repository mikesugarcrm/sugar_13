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

class SugarTestAbstractMetaDataParserUtilities
{
    /**
     * @param array $fielddefs
     * @param array $constructorParams params required by the constructor call
     * @return SidecarGridLayoutMetaDataParser
     */
    public static function createGridLayoutParserWithFielddefs($fielddefs, $constructorParams): AbstractMetaDataParser
    {
        $parser = ParserFactory::getParser(
            $constructorParams[0],
            $constructorParams[1],
            $constructorParams[2],
            $constructorParams[3],
            $constructorParams[4]
        );
        $parser->_fielddefs = $fielddefs;

        return $parser;
    }

    public static function removeCreatedParser($parser): void
    {
        unset($parser);
    }
}
