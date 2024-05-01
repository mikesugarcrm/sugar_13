#!/usr/bin/env bash

# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

set -e

KARMA_CMD=$1

echo "Starting Xvfb..."
Xvfb "${DISPLAY}" -screen 0 "${GEOMETRY}" &

echo "Starting VNC Server..."
x11vnc -display "${DISPLAY}" -bg -nopw -xkb -usepw -shared -repeat -loop -forever > /usr/local/x11vnc.log 2>&1 &

# Run the command indicated in the first parameter
eval $KARMA_CMD
exit $?
