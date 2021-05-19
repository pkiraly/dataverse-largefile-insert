#!/usr/bin/env bash

source ./common.sh

OUTPUT=md5.csv
if [[ -f $OUTPUT ]]; then
  rm $OUTPUT
fi

echo "md5,path" | sed -r 's/,/\t/g' > $OUTPUT

find $UPLOAD_DIR -type f -exec md5sum {} \; | sed -r 's/  /\t/' | sed  "s|$UPLOAD_DIR||g" >> $OUTPUT
