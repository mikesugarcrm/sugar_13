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

class SugarTestAbstractMetaDataImplementationUtilities
{
    /**
     * @param array $viewdefs
     * @param array $constructorParams params required by the constructor call
     * @return DeployedMetaDataImplementation
     * @throws Exception Thrown if the provided view doesn't exist for this module
     */
    public static function createDeployedMetaDataImplementation(
        $viewdefs,
        $constructorParams
    ): DeployedMetaDataImplementation {

        $impl = new DeployedMetaDataImplementation($constructorParams[0], $constructorParams[1], $constructorParams[2]);
        $impl->setViewClient($constructorParams[2]);
        $impl->setViewdefs($viewdefs);

        return $impl;
    }

    public static function removeCreatedImplementation($impl): void
    {
        unset($impl);
    }
}
