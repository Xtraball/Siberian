#!/bin/bash

# Options
read -e -p "Hotfix name: " RELEASE
read -e -p "App mobile version: " NATIVE_VERSION
read -e -p "Single commit (input hash or no): " SINGLE_COMMIT

if [ $SINGLE_COMMIT = "no" ] || [ $SINGLE_COMMIT = "" ]
then
    read -e -p "Hash from: " HASH_FROM
    read -e -p "Hash to: " HASH_TO
else
    HASH_FROM=$SINGLE_COMMIT"~1"
    HASH_TO=$SINGLE_COMMIT
fi

# Paths
ROOT=$PWD
REPO=$ROOT
SIBERIAN=$REPO"/siberian"
BUILDS=$ROOT"/release-"$RELEASE""
TEMPLATES=$ROOT"/ci/templates"

# Options
ZIP_EXCLUDE="--exclude=*.DS_Store* --exclude=*.idea* --exclude=*.git* --exclude=*.localized*"

# Clean up previous builds
cd $BUILDS
rm -rf *update*
cd -
rm -f $ROOT"/change.txt"
rm -f $ROOT"/delete.txt"
mkdir -p $BUILDS
mkdir -p $BUILDS/hotfix

# Files to include (or not)
git diff --name-status --relative=siberian/ $HASH_FROM $HASH_TO |grep "^\(M\|A\|R\|T\)" |cut -f 2 > $ROOT"/change.txt"
git diff --name-status --relative=siberian/ $HASH_FROM $HASH_TO |grep "^D" |cut -f 2 > $ROOT"/delete.txt"

# Formattings files to delete
TODELETE=""
while read FILE; do
    TODELETE=$TODELETE"\""$FILE"\","
done < $ROOT"/delete.txt"
TODELETE=$(echo $TODELETE | sed -e 's/\//\\\//g' -e 's/\(,$\)//g')

# Building SAE - Update
while read FILE; do
  DIRNAME=$(dirname $SIBERIAN"/"$FILE)
  BASEDIR=${DIRNAME/$SIBERIAN/}
  if [ ! -d $BUILDS"/hotfix/"$BASEDIR ];then
    mkdir -p $BUILDS"/hotfix/"$BASEDIR
  fi
  cp $SIBERIAN"/"$FILE $BUILDS"/hotfix/"$BASEDIR
done < $ROOT"/change.txt"
# Clean up new logo
rm -rf $BUILDS/hotfix/app/sae/design/desktop/flat/images/header/logo.png
cat $TEMPLATES/package-hotfix.json | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" \
    -e "s/%TYPE_LOWER%/sae/g" \
    -e s/%TO_DELETE%/$TODELETE/g > $BUILDS/hotfix/package.json
# Dive into folder then zip
cd $BUILDS/hotfix/
zip -r -9 $ZIP_EXCLUDE ../hotfix.$RELEASE.zip ./

# Clean-up
rm -rf $BUILDS/hotfix
rm -f $ROOT"/delete.txt"
rm -f $ROOT"/change.txt"