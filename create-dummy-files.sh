
#!/usr/bin/env bash

source ./common.sh

DIR=dummy
if [[ -d $DIR ]]; then
  rm -rf $DIR
fi

mkdir $DIR

find $UPLOAD_DIR -type f -print | sed  "s|$UPLOAD_DIR|$DIR/|g" | xargs -I % sh -c 'mkdir -p $(dirname %) ; touch %'

