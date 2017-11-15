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
mkdir -p $BUILDS/update-sae
mkdir -p $BUILDS/update-mae
mkdir -p $BUILDS/update-pe

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
  if [ ! -d $BUILDS"/update-sae/"$BASEDIR ];then
    mkdir -p $BUILDS"/update-sae/"$BASEDIR
  fi
  cp $SIBERIAN"/"$FILE $BUILDS"/update-sae/"$BASEDIR
done < $ROOT"/change.txt"

# Force include files
#mkdir -p $BUILDS"/update-sae/var/apps/ionic/"
#cp -Rf $SIBERIAN"/var/apps/ionic/previewer" $BUILDS"/update-sae/var/apps/ionic/"

rm -rf $BUILDS/update-sae/app/pe
rm -rf $BUILDS/update-sae/app/mae
php -f $REPO"/ci/scripts/manifest.php" sae $SIBERIAN"/" $BUILDS"/update-sae/"
# Clean up new logo
rm -rf $BUILDS/update-sae/app/sae/design/desktop/flat/images/header/logo.png
mkdir -p $BUILDS/update-sae/lib/Siberian/
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" > $BUILDS/update-sae/lib/Siberian/Version.php
cat $TEMPLATES/package-update.json | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Single App Edition/g" \
    -e "s/%TYPE%/SAE/g" \
    -e "s/%TYPE_LOWER%/sae/g" \
    -e s/%TO_DELETE%/$TODELETE/g \
    -e s/%REQUIRED_VERSION%/$REQUIRED_VERSION/g > $BUILDS/update-sae/package.json
# Dive into folder then zip
cd $BUILDS/update-sae/
zip -r -9 $ZIP_EXCLUDE ../siberian_sae.update.$RELEASE.zip ./


# Building MAE - Update
while read FILE; do
  DIRNAME=$(dirname $SIBERIAN"/"$FILE)
  BASEDIR=${DIRNAME/$SIBERIAN/}
  if [ ! -d $BUILDS"/update-mae/"$BASEDIR ];then
    mkdir -p $BUILDS"/update-mae/"$BASEDIR
  fi
  cp $SIBERIAN"/"$FILE $BUILDS"/update-mae/"$BASEDIR
done < $ROOT"/change.txt"

# Force include files
#mkdir -p $BUILDS"/update-mae/var/apps/ionic/"
#cp -Rf $SIBERIAN"/var/apps/ionic/previewer" $BUILDS"/update-mae/var/apps/ionic/"


rm -rf $BUILDS/update-mae/app/pe
php -f $REPO"/ci/scripts/manifest.php" mae $SIBERIAN"/" $BUILDS"/update-mae/"
# Clean up new logo
rm -rf $BUILDS/update-sae/app/sae/design/desktop/flat/images/header/logo.png
mkdir -p $BUILDS/update-mae/lib/Siberian/
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Multi-Apps Edition/g" \
    -e "s/%TYPE%/MAE/g" > $BUILDS/update-mae/lib/Siberian/Version.php
cat $TEMPLATES/package-update.json | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Multi-Apps Edition/g" \
    -e "s/%TYPE%/MAE/g" \
    -e "s/%TYPE_LOWER%/mae/g" \
    -e s/%TO_DELETE%/$TODELETE/g \
    -e s/%REQUIRED_VERSION%/$REQUIRED_VERSION/g > $BUILDS/update-mae/package.json
# Dive into folder then zip
cd $BUILDS/update-mae/
zip -r -9 $ZIP_EXCLUDE ../siberian_mae.update.$RELEASE.zip ./


# Building PE - Update
while read FILE; do
  DIRNAME=$(dirname $SIBERIAN"/"$FILE)
  BASEDIR=${DIRNAME/$SIBERIAN/}
  if [ ! -d $BUILDS"/update-pe/"$BASEDIR ];then
    mkdir -p $BUILDS"/update-pe/"$BASEDIR
  fi
  cp $SIBERIAN"/"$FILE $BUILDS"/update-pe/"$BASEDIR
done < $ROOT"/change.txt"

# Force include files
#mkdir -p $BUILDS"/update-pe/var/apps/ionic/"
#cp -Rf $SIBERIAN"/var/apps/ionic/previewer" $BUILDS"/update-pe/var/apps/ionic/"

php -f $REPO"/ci/scripts/manifest.php" pe $SIBERIAN"/" $BUILDS"/update-pe/"
# Clean up new logo
rm -rf $BUILDS/update-sae/app/sae/design/desktop/flat/images/header/logo.png
mkdir -p $BUILDS/update-pe/lib/Siberian/
cat $REPO/bin/templates/Version.php | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Platform Edition/g" \
    -e "s/%TYPE%/PE/g" > $BUILDS/update-pe/lib/Siberian/Version.php
cat $TEMPLATES/package-update.json | \
    sed -e s/%VERSION%/$RELEASE/g \
    -e s/%NATIVE_VERSION%/$NATIVE_VERSION/g \
    -e s/%API_VERSION%/$API_VERSION/g \
    -e "s/%NAME%/Platform Edition/g" \
    -e "s/%TYPE%/PE/g" \
    -e "s/%TYPE_LOWER%/pe/g" \
    -e s/%TO_DELETE%/$TODELETE/g \
    -e s/%REQUIRED_VERSION%/$REQUIRED_VERSION/g > $BUILDS/update-pe/package.json
# Dive into folder then zip
cd $BUILDS/update-pe/
zip -r -9 $ZIP_EXCLUDE ../siberian_pe.update.$RELEASE.zip ./

# Clean-up
rm -rf $BUILDS/update-sae
rm -rf $BUILDS/update-mae
rm -rf $BUILDS/update-pe
rm -f $ROOT"/delete.txt"
rm -f $ROOT"/change.txt"