# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM node:10.24.1
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

# Install CI Utilities
COPY scripts/install-ci-utils.sh /opt/bin/install-ci-utils.sh
RUN chmod +x /opt/bin/install-ci-utils.sh && \
    /opt/bin/install-ci-utils.sh

ADD scripts/Thorn.Entrypoint.sh /Thorn.Entrypoint.sh

# Default command to run when container starts:
ENTRYPOINT ["/Thorn.Entrypoint.sh"]
