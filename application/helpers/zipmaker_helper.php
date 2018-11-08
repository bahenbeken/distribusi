<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function zipFilesAndDownload($file_names,$archive_file_name,$file_path)
{
  //create the object
  $zip = new ZipArchive();
  //create the file and throw the error if unsuccessful
  if ($zip->open($archive_file_name, ZIPARCHIVE::CREATE )!==TRUE) {
    exit("cannot open <$archive_file_name>\n");
  }

  //add each files of $file_name array to archive
  foreach($file_names as $files)
  {
    $zip->addFile($file_path.$files,$files);
  }
  $zip->close();

  //then send the headers to foce download the zip file
  header("Content-type: application/zip");
  header("Content-Disposition: attachment; filename=$archive_file_name");
  header("Pragma: no-cache");
  header("Expires: 0");
  readfile("$archive_file_name");
  exit;
}