#!/bin/bash

# Git hash, version
RELEASE=$(node -e "console.log(require('./package.json').version);")
REQUIRED_VERSION=$(node -e "console.log(require('./package.json').lastversion);")
NATIVE_VERSION=$(node -e "console.log(require('./package.json').nativeVersion);")
API_VERSION=$(node -e "console.log(require('./package.json').apiVersion);")
HASH_FROM=$(git rev-parse "v"$REQUIRED_VERSION^0)
HASH_TO=$(git rev-parse "v"$RELEASE^0)

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
mkdir -p $BUILDS/update-package

# Files to include (or not)
git diff --name-status --relative=siberian/ $HASH_FROM $HASH_TO |grep "^\(M\|A\|R\|T\)" |cut -f 2 > $ROOT"/change.txt"
git diff --name-status --relative=siberian/ $HASH_FROM $HASH_TO |grep "^D" |cut -f 2 > $ROOT"/delete.txt"

# Force delete files
if [ -f $ROOT"/ci/override/force-delete.txt" ]
then
    cat $ROOT"/ci/override/force-delete.txt" >> $ROOT"/delete.txt"
fi

# Add a line return otherwise the latest file will not be deleted
echo "" >> $ROOT"/delete.txt"

# Formattings files to delete
TODELETE=""
while read FILE; do
    if [ $FILE != "" ]
    then
        TODELETE=$TODELETE"\""$FILE"\","
    fi
done < $ROOT"/delete.txt"
TODELETE=$(echo $TODELETE | sed -e 's/\//\\\//g' -e 's/\(,$\)//g')

# Building SAE - Update
while read FILE; do
  DIRNAME=$(dirname $SIBERIAN"/"$FILE)
  BASEDIR=${DIRNAME/$SIBERIAN/}
  if [ ! -d $BUILDS"/update-package/"$BASEDIR ];then
    mkdir -p $BUILDS"/update-package/"$BASEDIR
  fi
  cp $SIBERIAN"/"$FILE $BUILDS"/update-package/"$BASEDIR
done < $ROOT"/change.txt"

php -f $REPO"/ci/scripts/manifest.php" sae $SIBERIAN"/" $BUILDS"/update-package/"

# Clean up new logo
rm -rf $BUILDS/update-package/app/sae/design/desktop/flat/images/header/logo.png
mkdir -p $BUILDS/update-package/lib/Siberian/
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" > $BUILDS/update-package/lib/Siberian/Version.php
cat $TEMPLATES/package-update.json | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" \
    -e "s/%TYPE_LOWER%/sae/g" \
    -e s/%TO_DELETE%/$TODELETE/g \
    -e s/%REQUIRED_VERSION%/$REQUIRED_VERSION/g > $BUILDS/update-package/package.json
# Dive into folder then zip
cd $BUILDS/update-package/
zip -r -9 $ZIP_EXCLUDE ../sae.update.$RELEASE.zip ./


# Clean-up
rm -rf $BUILDS/update-package
rm -f $ROOT"/delete.txt"
rm -f $ROOT"/change.txt"