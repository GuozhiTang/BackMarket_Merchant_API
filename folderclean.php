<?php
$flag = 0;
// ignore_user_abort();
set_time_limit(0);
$interval = 3000;
do {
  $flagfile = "cleanflag.txt";
  if (file_exists($flagfile) && is_readable($flagfile)) {
    $fh = fopen($flagfile, "r");
    while (!feof($fh)) {
      $flag = fgets($fh);
    }
    fclose($fh);
  }
  $dir = "./testlog";
  $handle = opendir("{$dir}/");
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != ".." && !is_dir("{$dir}/{$file}")) {
      @unlink("{$dir}/{$file}");
      // @unlink($dir."/".$file);
      // echo("{$dir}/{$file}");
      // echo $file."-----------";
    }
  }
  closedir($handle);
  sleep($interval);
} while ($flag);
?>