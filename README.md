# Dataverse large file insert tool

Due to the problems with the underying Java application server (Payara or Glassfish) it is not possible to upload large files (> 2 GB) via [Dataverse](https://github.com/IQSS/dataverse) the web user interface neither the API. This set of scripts provides a way to insert file information to Dataverse once the files has been uploaded to the server. The base idea behind this is that as administrator somehow you can find a way outside of Dataverse to upload the large files into a server and save them into some directory. The scripts 
* create dummy files with the same name, extension and directory structure but with 0 size, and upload them to a given dataset via API
* fetch all information from the uploaded files (size, MD5 hash) and from the database (file IDs, real location) and merge these information together
* copy the files into its proper place
* update the database with the real file size and md5 hash 

Note: there is tool called [Data Capture Module](https://github.com/sbgrid/data-capture-module) (see [usage description](https://guides.dataverse.org/en/5.3/developers/big-data-support.html?highlight=large#data-capture-module-dcm) in Dataverse documentation) but I had problems with it, so finally created these scripts instead.

## Usage
### create configuration

```
cp common.template common.sh
chmod +x ./common.sh
```

edit common.sh and adjust the following variables:

* `SERVER_URL` the URL of the Dataverse instance
* `API_KEY` the Dataverse API key (the owner of the key has the right to add files)
* `PERSISTENT_IDENTIFIER` the persistent identifier of the dataset
* `BASE_DIR` the directory where Dataverse stores the files
* `UPLOAD_DIR` the directory of the uploaded files

### upload files to the UPLOAD_DIR directory

* delete current content of UPLOAD_DIR
* upload new files to UPLOAD_DIR

### create and upload dummy files
```
./create-dummy-files.sh
./upload-dummy-files.sh
```

### extract information for individual files
```
./list-file-size.sh
./create-md5.sh
./retrieve-files-from-api.sh
```

### merge all information together
```
source common.sh
php merge-info.php
```

It creates two files
* `copy-files.sh` commands to copy files 
* `update-file-info.sql` SQL command to update file information

### copy files from the UPLOAD_DIR to its registered place with rename to its registered name
```
chmod +x copy-files.sh
sudo -u dataverse ./copy-files.sh
```

### update database with MD5 and file size
```
psql -U [username] -W dvndb -f update-file-info.sql
```
