source ./common.sh

echo "id,filename,storageIdentifier" | sed -r 's/,/\t/g' > data.csv
curl -s -H X-Dataverse-key:$API_KEY "$SERVER_URL/api/datasets/:persistentId/?persistentId=$PERSISTENT_IDENTIFIER" \
  | jq '.data.latestVersion.files[] | .dataFile | .id, .filename, .storageIdentifier, "___"' \
  | paste -s -d "\t" \
  | sed -r 's/\t"___"\t?/\n/g' \
  >> data.csv

