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

namespace Sugarcrm\Sugarcrm\CloudDrive\Paths\Model\Types;

use Doctrine\DBAL\Exception as DBALException;
use Exception;
use SugarApiExceptionNotFound;
use Sugarcrm\Sugarcrm\CloudDrive\Paths\Model\Types\CloudDrivePathBase;
use SugarQueryException;

class CloudDrivePathGoogleDrive extends CloudDrivePathBase
{
    /**
     * Gets the google drive path
     *
     * @param array $options
     * @return array
     */
    public function getDrivePath(array $options): array
    {
        $result = [
            'root' => 'root',
        ];

        $paths = [];
        $record = $this->getRecord($options);
        $rootPath = $this->findRoot($options['type']);

        if (isset($options['layoutName']) && $options['layoutName'] === 'record') {
            $paths = $this->getPaths($options);
        }

        try {
            if (safeCount($paths) > 0) {
                $pathDetails = $paths[0];
                $path = $pathDetails['path'];
                $path = $this->parsePath($path, $record);

                return $this->getBasePath($pathDetails, $path, $options);
            }

            return $this->getRootPath($rootPath, $options);
        } catch (Exception $e) {
            throw $e;
        }

        return $result;
    }
}