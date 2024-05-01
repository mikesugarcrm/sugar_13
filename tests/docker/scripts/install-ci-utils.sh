#!/bin/bash

# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

#========================
# Install CI Stuffs
#========================
echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
apt-get update -qqy
DEBIAN_FRONTEND=noninteractive
apt-get install -qqy --force-yes --no-install-recommends \
  apt-transport-https \
  build-essential \
  ca-certificates \
  lsb-release \
  python \
  rlwrap \
  software-properties-common \
  vim \
  curl \
  wget \
  git \
  zip \
  unzip
