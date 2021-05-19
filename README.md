# Dataverse large file insert tool

Due to the problems with the underying Java application server (Payara or Glassfish) it is not possible to upload large files (> 2 GB) via the web user interface neither the API. This set of scripts provides a way to insert file information to Dataverse once the files has been uploaded to the server.

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
sudo -u dataverse ./copy-files.sh

# update database with MD5 and file size
psql -U [username] -W dvndb -f update-file-info.sql
