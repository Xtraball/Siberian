#!/bin/bash

# Paths
ROOT=$PWD
SIBERIAN=$ROOT"/siberian"

# Options
TAR_EXCLUDE="--options gzip:9 --exclude='*.DS_Store*' --exclude='*.idea*' --exclude='*.gitignore*' --exclude='*.localized*'"

# Pack archives restore!
cd $SIBERIAN/var/apps/ionic
tar $EXCLUDE -czf ./android.tgz ./android
tar $EXCLUDE -czf ./ios.tgz ./ios
tar $EXCLUDE -czf ./ios-noads.tgz ./ios-noads
tar $EXCLUDE -czf ./previewer.tgz ./previewer

cd $SIBERIAN/var/apps
tar $EXCLUDE -czf ./browser.tgz ./browser
