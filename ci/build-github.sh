#!/bin/bash

# Git hash, version
RELEASE=$(node -e "console.log(require('./package.json').version);")
NATIVE_VERSION=$(node -e "console.log(require('./package.json').nativeVersion);")
API_VERSION=$(node -e "console.log(require('./package.json').apiVersion);")
HASH_TO=$(git rev-parse "g"$RELEASE^0)

# Testing release, if stable or beta, push to develop when beta.
if $( echo $RELEASE | grep --quiet 'beta' )
then
	GH_BRANCH="develop"
	URL_UPDATES="beta-updates02"
else
	GH_BRANCH="master"
	URL_UPDATES="updates02"
fi

# Paths
ROOT=$PWD
REPO=$ROOT
SIBERIAN=$REPO"/siberian"
BUILDS=$ROOT"/release-"$RELEASE""
TEMPLATES=$ROOT"/ci/templates"
GSAE=$BUILDS"/github-sae"

# Options
TAR_EXCLUDE="--options gzip:9 --exclude='*.DS_Store*' --exclude='*.idea*' --exclude='*.gitignore*' --exclude='*.localized*'"

# Clean up previous builds
mkdir -p $BUILDS

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

cd $SIBERIAN/var/apps/ionic
tar $EXCLUDE -czf ./android.tgz ./android
tar $EXCLUDE -czf ./ios.tgz ./ios
tar $EXCLUDE -czf ./ios-noads.tgz ./ios-noads
tar $EXCLUDE -czf ./previewer.tgz ./previewer

cd $SIBERIAN/var/apps
tar $EXCLUDE -czf ./browser.tgz ./browser

cd $ROOT

# Checkout http://github.com/Xtraball/SiberianCMS
git clone git@github.com:Xtraball/SiberianCMS.git $GSAE
cd $GSAE
git config core.fileMode false
git config --global user.email "dev@xtraball.com"
git config --global user.name "SiberianCMS via Jenkins CI"
git checkout $GH_BRANCH

cd $ROOT

# Building SAE - Install
cp -rp $SIBERIAN/* $GSAE/
rm -rf $GSAE/app/pe
rm -rf $GSAE/app/mae
php -f $REPO"/ci/scripts/manifest.php" sae $SIBERIAN"/" $GSAE"/"
# Version.php
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" > $GSAE/lib/Siberian/Version.php

# Files to delete
rm -f $ROOT"/delete.txt"
git diff --name-status --relative=siberian/ $HASH_FROM $HASH_TO |grep "^D" |cut -f 2 > $ROOT"/delete.txt"

# Dive back into folder
cd $GSAE

# Formatting files to delete
while read FILE; do
    rm -f $GSAE"/"$FILE
done < $ROOT"/delete.txt"

# Creating the commit
git add --all
git commit -m "Version $RELEASE
--
See full release note here https://"$URL_UPDATES".siberiancms.com/release-notes/all/$RELEASE.html
"

git tag v"$RELEASE"
git push origin $GH_BRANCH
git push --tags origin $GH_BRANCH

# Clean-up
rm -rf $GSAE
rm -f $ROOT"/delete.txt"

