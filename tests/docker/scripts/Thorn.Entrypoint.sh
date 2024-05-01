#!/bin/bash

# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

# Ensure that the required mountpoint exists:
if [[ ! -d "/sugarcrm" ]]; then
    echo "You must mount your sugarcrm directory in the container as /sugarcrm."
    echo "Example:  docker run -v \$PWD:/sugarcrm ..."
    exit 1
fi

cd /sugarcrm

if [[ -z "${DEV}" ]]; then
    yarn install
    node_modules/gulp/bin/gulp.js test:rest "$@"
else
    /bin/bash
fi
