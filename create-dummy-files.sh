#!/usr/bin/env bash

echo "before"
source ./common.sh
echo "after"

DIR=dummy
if [[ -d $DIR ]]; then
  rm -rf $DIR
fi

mkdir $DIR

find $UPLOAD_DIR -type f -print | sed  "s|$UPLOAD_DIR|$DIR/|g" | xargs -I % sh -c 'mkdir -p "$(dirname "%")" ; touch "%"'

echo "dummy files have been created in '${DIR}' directory"
