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
# Install Virtual Frame buffer (Graphical Support) and VNC Server
#========================
apt-get update -qqy
apt-get install -qqy --force-yes --no-install-recommends \
    xvfb \
    x11vnc

#========================
# Configure VNC
#========================
mkdir -p ~/.vnc && \
x11vnc -storepasswd '' ~/.vnc/passwd

#========================
# Install Google Chrome (Latest Stable Version)
#=======================
wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
echo "deb http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google.list
apt-get update -qqy 
apt-get install -qqy google-chrome-stable

#========================
# Install Firefox
#========================
wget --no-verbose -O /tmp/firefox.tar.bz2 https://download-installer.cdn.mozilla.net/pub/firefox/releases/$FIREFOX_VERSION/linux-x86_64/en-US/firefox-$FIREFOX_VERSION.tar.bz2
tar -C /opt -xf /tmp/firefox.tar.bz2
rm /tmp/firefox.tar.bz2
mv /opt/firefox /opt/firefox-$FIREFOX_VERSION
ln -fs /opt/firefox-$FIREFOX_VERSION/firefox /usr/bin/firefox

# To properly use this shellscript your entrypoint script must run xvfb and
# x11vnc, like this:
#
# Xvfb "${DISPLAY}" -screen 0 "${GEOMETRY}" &
# x11vnc -display "${DISPLAY}" -bg -nopw -xkb -usepw -shared -repeat -loop -forever > /usr/local/x11vnc.log 2>&1 &
