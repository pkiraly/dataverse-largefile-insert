#!/usr/bin/env bash

source ./common.sh

export DIR=dummy

upload_file() {
  FILENAME=$1
  echo "Uploading $FILENAME"
  DIRECTORY=$(echo $(dirname $FILENAME) | sed "s|$DIR/||")
  JSON='jsonData={"description":"","directoryLabel":"'$DIRECTORY'","categories":["Data"], "restrict":"false"}'

  curl -H X-Dataverse-key:$API_KEY \
       -X POST \
       -F "file=@$FILENAME" \
       -F "$JSON" \
       "$SERVER_URL/api/datasets/:persistentId/add?persistentId=$PERSISTENT_IDENTIFIER"
}

export -f upload_file
find $DIR -type f -exec bash -c 'upload_file "$0"' {} \;

