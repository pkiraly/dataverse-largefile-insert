<?php
# merges file size, MD5 hash and other file information
# and generates copy-files.sh and update-file-info.sql

define('LN', "\n");

$config = readConfig();
# echo 'config: ', "\n";
# print_r($config);
# echo '/config ', "\n";

$records = readRecords();
echo 'records: ', "\n";
print_r($records);
echo '/records: ', "\n";

if (file_exists('copy-files.sh'))
  unlink('copy-files.sh');
if (file_exists('update-file-info.sql'))
  unlink('update-file-info.sql');

foreach ($records as $record) {
  $copy_cmd = sprintf(
      "cp %s/%s %s/%s/%s\n",
      $config['UPLOAD_DIR'] . $config['SUFFIX'], $record->path,
      $config['BASE_DIR'], $config['PERSISTENT_IDENTIFIER'], $record->storageIdentifier2
    );
  echo $copy_cmd, "\n";
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
  // echo 'md5', LN;
  # echo json_encode($md5), LN;
  // echo '/md5', LN;

  $all = [];
  foreach ($sizes as $path => $record) {
    if (isset($md5[$path])) {
      $record->md5 = $md5[$path]->md5;
      $filename = preg_replace('/(^.*\/)?/', '', $record->path);
      $all[$filename] = $record;
    } else {
      echo $path, ' ', json_encode($record), ' is not in MD5 array', LN;
    }
  }
  echo "all:\n";
  print_r($all);
  echo "/all\n";


  $data = readCsv('data.csv', 'filename', "\t");
  # echo "data:\n";
  # print_r($data);
  # echo "/data\n";
  foreach ($data as $filename => $record) {
    $record->filename = str_replace('"', '', $record->filename);
    $record->storageIdentifier = str_replace('"', '', $record->storageIdentifier);
    $record->storageIdentifier2 = str_replace('file://', '', $record->storageIdentifier);
    if (isset($all[md5($record->filename)])) {
      $record2 = $all[$record->filename];
      $record->md5 = $record2->md5;
      $record->size = $record2->size;
      $record->path = $record2->path;
    } else {
      echo json_encode($record), ' is not in MD5 array', LN;
    }


  }
  echo "data:\n";
  print_r($data);
  echo "/data\n";
  $full_records = array_filter($data, function($record) {
    return isset($record->md5) && !empty($record->md5)
           && isset($record->size) && !empty($record->size)
           && isset($record->path) && !empty($record->path);
  });
  return $full_records;
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
            $records[md5(trim($record->{$id}))] = $record;
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
