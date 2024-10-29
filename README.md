```
cp common.template common.sh
chmod +x ./common.sh
```

# * edit common.sh, adjust DOI, BASE_DIR
# * delete current content of BASE_DIR
# * upload new files to BASE_DIR
# then...

# create and upload dummy files
./create-dummy-files.sh
./upload-dummy-files.sh

# extract information for individual files
./list-file-size.sh
./create-md5.sh
./retrieve-files-from-api.sh

# merge all information together
php merge-info.php

# copy files
chmod +x copy-files.sh
sudo -u glassfish ./copy-files.sh

# update database with MD5 and file size
psql -U [username] -W dvndb -f update-file-info.sql
