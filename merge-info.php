<?php
# merges file size, MD5 hash and other file information
# and generates copy-files.sh and update-file-info.sql

$config = readConfig();

$records = readRecords();
print_r($records);

if (file_exists('copy-files.sh'))
  unlink('copy-files.sh');
if (file_exists('update-file-info.sql'))
  unlink('update-file-info.sql');

foreach ($records as $record) {
  $copy_cmd = sprintf(
      "cp %s/%s %s/%s/%s\n",
      $config['UPLOAD_DIR'], $record->path,
      $config['BASE_DIR'], $config['PERSISTENT_IDENTIFIER'], $record->storageIdentifier2
    );
  $copy_cmd = str_replace('//', '/', $copy_cmd);
  file_put_contents('copy-files.sh', $copy_cmd, FILE_APPEND);

  file_put_contents('update-file-info.sql',
    sprintf(
      "UPDATE datafile SET checksumvalue = '%s', filesize = %d WHERE id = %d;\n",
      $record->md5, $record->size, $record->id
    ),
    FILE_APPEND
  );
}

function readRecords() {

  $sizes = readCsv('sizes.csv', 'path', "\t");
  $md5 = readCsv('md5.csv', 'path', "\t");

  $all = [];
  foreach ($sizes as $path => $record) {
    $record->md5 = $md5[$path]->md5;
    $filename = preg_replace('/(^.*\/)?/', '', $path);
    $all[$filename] = $record;
  }
  # print_r($all);

  $data = readCsv('data.csv', 'filename', "\t");
  foreach ($data as $filename => $record) {
    $record->filename = str_replace('"', '', $record->filename);
    $record->storageIdentifier = str_replace('"', '', $record->storageIdentifier);
    $record->storageIdentifier2 = str_replace('file://', '', $record->storageIdentifier);
    $record2 = $all[$record->filename];
    $record->md5 = $record2->md5;
    $record->size = $record2->size;
    $record->path = $record2->path;
  }
  return $data;
}

function readCsv($csvFile, $id = '', $separator = ",") {
  $records = [];
  if (file_exists($csvFile)) {
    $lineNumber = 0;
    $header = [];

    foreach (file($csvFile) as $line) {
      if (strlen($line) > 1) {
        $lineNumber++;
        $values = str_getcsv($line, $separator);
        if ($lineNumber == 1) {
          $header = $values;
        } else {
          if (count($header) != count($values)) {
            error_log(sprintf('error in %s line #%d: %d vs %d', $csvFile, $lineNumber, count($header), count($values)));
          }
          $record = (object)array_combine($header, $values);
          if ($id != '' && isset($record->{$id})) {
            $records[$record->{$id}] = $record;
          } else {
            $records[] = $record;
          }
        }
      }
    }
  } else {
    error_log('file does not exist! ' . $csvFile);
  }
  return $records;
}

function readConfig() {
  $config_raw = parse_ini_file('common.sh');
  $config = [];
  foreach ($config_raw as $key => $value) {
    $clean_key = str_replace('export ', '', $key);
    $config[$clean_key] = ($clean_key === 'PERSISTENT_IDENTIFIER')
                        ? str_replace('doi:', '', $value)
                        : $value;
  }
  return $config;
}
