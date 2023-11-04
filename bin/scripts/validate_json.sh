#!/bin/bash

files=`find ./siberian -name '*.json'`

echo "Validating JSON files..."

for f in $files; do

  # skip file paths that contains node_modules
  if [[ $f == *"node_modules"* ]]; then
    continue
  fi

  echo $f
  cat $f | jq empty
  if [[ "$?" -ne 0 ]]; then
    echo "ERROR: $f"
    exit 1 # must abort on error
  fi
done
exit 0