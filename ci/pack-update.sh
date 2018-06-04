#!/bin/bash

# Git hash, version
RELEASE=$(node -e "console.log(require('./package.json').version);")
REQUIRED_VERSION=$(node -e "console.log(require('./package.json').lastversion);")
NATIVE_VERSION=$(node -e "console.log(require('./package.json').nativeVersion);")
API_VERSION=$(node -e "console.log(require('./package.json').apiVersion);")
HASH_FROM=$(git rev-parse --quiet --verify "v"$REQUIRED_VERSION^0)
HASH_TO=$(git rev-parse --quiet --verify "v"$RELEASE^0)

if [ -z "$HASH_FROM" ]
then
    echo "There is no HASH_FROM available, skip building updates."
    exit 0
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
mkdir -p $BUILDS
cd $BUILDS
rm -rf *update*
cd -
rm -f $ROOT"/change.txt"
rm -f $ROOT"/delete.txt"
mkdir -p $BUILDS/update-package

# Files to include (or not)
git config diff.renameLimit 100000

EXTRA_CHANGE=`cat $ROOT/ci/override/extra-change.txt`
EXTRA_DELETE=`cat $ROOT/ci/override/extra-delete.txt`
CHANGES=$ROOT"/change.txt"
DELETES=$ROOT"/delete.txt"

eval "git diff --name-status --diff-filter=MACT --relative=siberian/ $HASH_FROM $HASH_TO $EXTRA_CHANGE |cut -f 2 > $CHANGES"
# For renamed files we take the second argument (new name)
eval "git diff --name-status --diff-filter=R --relative=siberian/ $HASH_FROM $HASH_TO $EXTRA_CHANGE |cut -f 3 >> $CHANGES"
# renamed old name have to be deleted
eval "git diff --name-status --diff-filter=R --relative=siberian/ $HASH_FROM $HASH_TO |cut -f 2 >> $DELETES"
eval "git diff --name-status --diff-filter=D --relative=siberian/ $HASH_FROM $HASH_TO |cut -f 2 >> $DELETES"

# Force delete files
if [ -f $ROOT"/ci/override/force-delete.txt" ]
then
    cat $ROOT"/ci/override/force-delete.txt" >> $DELETES
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

# package.json
PACKAGE_SAE='{
  "name": "Single App Edition",
  "version": "'$RELEASE'",
  "code": "",
  "description": "<a href=\"https://updates02.siberiancms.com/release-notes/all/'$RELEASE'.html\" target=\"_blank\">Click here to see the release notes</a>",
  "release_note": {
    "url": "https://updates02.siberiancms.com/release-notes/all/'$RELEASE'.html",
    "show": true,
    "is_major": true
  },
  "dependencies": {
    "system": {
      "type": "SAE",
      "version": "'$REQUIRED_VERSION'"
    }
  },
  "files_to_delete": ['$TODELETE']
}'

# Building SAE - Update
while read FILE; do
  TMP=$(printf %q "${FILE}");
  DIRNAME=$(dirname $SIBERIAN"/"$TMP)
  BASEDIR=${DIRNAME/$SIBERIAN/}
  if [ ! -d $BUILDS"/update-package/"$BASEDIR ];then
    mkdir -p $BUILDS"/update-package/"$BASEDIR
  fi
  SRC=$SIBERIAN"/"$TMP
  DST=$BUILDS"/update-package/"$(printf %q "${BASEDIR}")
  echo "cp $SRC $DST"
  cp $SRC $DST
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

# Clean archives
rm -f $BUILDS/update-mae/var/apps/ionic/android.tgz
rm -f $BUILDS/update-mae/var/apps/ionic/ios.tgz
rm -f $BUILDS/update-mae/var/apps/browser.tgz

zip -r -9 $ZIP_EXCLUDE ../sae.update.$RELEASE.zip ./


# Clean-up
#rm -rf $BUILDS/update-package
#rm -f $ROOT"/delete.txt"
#rm -f $ROOT"/change.txt"