#!/bin/bash

# Git hash, version
RELEASE=$(node -e "console.log(require('./package.json').version);")
NATIVE_VERSION=$(node -e "console.log(require('./package.json').nativeVersion);")
API_VERSION=$(node -e "console.log(require('./package.json').apiVersion);")

# Paths
ROOT=$PWD
REPO=$ROOT
SIBERIAN=$REPO"/siberian"
BUILDS=$ROOT"/release-"$RELEASE""
TEMPLATES=$ROOT"/ci/templates"
INSTALL_DIR=$BUILDS"/sae-installer"

# Options
TAR_EXCLUDE="--exclude='*.DS_Store*' --exclude='*.idea*' --exclude='*.git*' --exclude='*.localized*'"

# Clean up previous builds
mkdir -p $BUILDS
cd $BUILDS
rm -rf "*install*"
cd -
rm -f $ROOT"/change.txt"
rm -f $ROOT"/delete.txt"
mkdir -p $INSTALL_DIR

# Building SAE - Install
cp -rp $SIBERIAN/* $INSTALL_DIR/
php -f $REPO"/ci/scripts/manifest.php" sae $SIBERIAN"/" $BUILDS"/sae-installer/"

# Version.php
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" > $INSTALL_DIR/lib/Siberian/Version.php

# Dive into folder then archive
cd $INSTALL_DIR
tar $EXCLUDE -czf ../sae-installer.tgz ./

# Clean-up
rm -rf $INSTALL_DIR