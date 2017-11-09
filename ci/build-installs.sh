#!/bin/bash

# Git hash, version
RELEASE=$(node -e "console.log(require('./package.json').version);")
NATIVE_VERSION=$(node -e "console.log(require('./package.json').nativeVersion);")
API_VERSION=$(node -e "console.log(require('./package.json').apiVersion);")
HASH_TO=$(git rev-parse "v"$RELEASE^0)

# Builds hashes
HSAE="xoNJw0qczqXUsRQqW0ubNGUXHtmxNANp"
HMAE="CP0BShFJYq4EUcPdAX226ynhTsdrDx5V"
HPE="f3RgrwZkkWqZl0UMUTFQXjvrU21zyyJR"

# Paths
ROOT=$PWD
REPO=$ROOT
SIBERIAN=$REPO"/siberian"
BUILDS=$ROOT"/release-"$RELEASE""
TEMPLATES=$ROOT"/ci/templates"
PSAE=$BUILDS"/install-sae"
PMAE=$BUILDS"/install-mae"
PPE=$BUILDS"/install-pe"

# Options
TAR_EXCLUDE="--exclude='*.DS_Store*' --exclude='*.idea*' --exclude='*.gitignore*' --exclude='*.localized*'"

# Clean up previous builds
cd $BUILDS
rm -rf "*install*"
cd -
rm -f $ROOT"/change.txt"
rm -f $ROOT"/delete.txt"
mkdir -p $BUILDS
mkdir -p $PSAE
mkdir -p $PMAE
mkdir -p $PPE

# Clean up just in case
rm -Rf $SIBERIAN/system.php
rm -Rf $SIBERIAN/var/schema
rm -Rf $SIBERIAN/var/tmp/*
rm -Rf $SIBERIAN/var/log/*
rm -Rf $SIBERIAN/var/cache/*
rm -Rf $SIBERIAN/var/apps/angular/iphone/*
rm -Rf $SIBERIAN/var/apps/angular/android/*
rm -Rf $SIBERIAN/var/apps/ionic/tools/android-sdk
rm -Rf $SIBERIAN/var/apps/ionic/tools/gradle

rm -Rf $SIBERIAN/images/templates/accessories
rm -Rf $SIBERIAN/images/templates/b_b
rm -Rf $SIBERIAN/images/templates/band
rm -Rf $SIBERIAN/images/templates/beauty_center
rm -Rf $SIBERIAN/images/templates/big_company
rm -Rf $SIBERIAN/images/templates/club
rm -Rf $SIBERIAN/images/templates/corporate
rm -Rf $SIBERIAN/images/templates/fitness
rm -Rf $SIBERIAN/images/templates/florist
rm -Rf $SIBERIAN/images/templates/football_team
rm -Rf $SIBERIAN/images/templates/grande_palace
rm -Rf $SIBERIAN/images/templates/jewellery
rm -Rf $SIBERIAN/images/templates/lawyer
rm -Rf $SIBERIAN/images/templates/nyc
rm -Rf $SIBERIAN/images/templates/paris
rm -Rf $SIBERIAN/images/templates/publisher
rm -Rf $SIBERIAN/images/templates/real_estate_one
rm -Rf $SIBERIAN/images/templates/rush_cafe
rm -Rf $SIBERIAN/images/templates/surgery


# Building SAE - Install
cp -rp $SIBERIAN/* $PSAE/
rm -rf $PSAE/app/pe
rm -rf $PSAE/app/mae
php -f $REPO"/ci/scripts/manifest.php" sae $SIBERIAN"/" $BUILDS"/install-sae/"
# Version.php
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" > $PSAE/lib/Siberian/Version.php
# Dive into folder then zip
cd $PSAE
tar $EXCLUDE -czf ../$RELEASE-$HSAE-install-sae.tgz ./


# Building MAE - Install
cp -rp $SIBERIAN/* $PMAE/
rm -rf $PMAE/app/pe
php -f $REPO"/ci/scripts/manifest.php" mae $SIBERIAN"/" $BUILDS"/install-mae/"
# Version.php
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Multi-Apps Edition/g" \
    -e "s/%TYPE%/MAE/g" > $PMAE/lib/Siberian/Version.php
# Dive into folder then zip
cd $PMAE
tar $EXCLUDE -czf ../$RELEASE-$HMAE-install-mae.tgz ./

# Building PE - Install
cp -rp $SIBERIAN/* $PPE/
php -f $REPO"/ci/scripts/manifest.php" pe $SIBERIAN"/" $BUILDS"/install-pe/"
# Version.php
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Platform Edition/g" \
    -e "s/%TYPE%/PE/g" > $PPE/lib/Siberian/Version.php
# Dive into folder then zip
cd $PPE
tar $EXCLUDE -czf ../$RELEASE-$HPE-install-pe.tgz ./

# Clean-up
rm -rf $PSAE
rm -rf $PMAE
rm -rf $PPE