source ./common.sh

OUTPUT=sizes.csv
if [[ -f $OUTPUT ]]; then
  rm $OUTPUT
fi

touch $OUTPUT

echo "size,path" | sed -r 's/,/\t/g' > $OUTPUT

find $UPLOAD_DIR -type f -exec ls -la {} \; | awk '{print $5 "\t" $9}'  | sed  "s|$UPLOAD_DIR||g" >> $OUTPUT

