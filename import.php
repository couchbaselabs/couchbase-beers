<?php

// Setup Couchbase connected objects
try {
  $cb = new Couchbase("127.0.0.1:8091", "", "", "beer-sample");
} catch (ErrorException $e) {
  die($e->getMessage());
}

// import a directory
function import($cb, $dir) {
  $d = dir($dir);
  while (false !== ($file = $d->read())) {
    if (substr($file, -5) != '.json') continue;
    echo "adding $file\n";
    echo $cb->set(substr($file, 0, -5), file_get_contents($dir . $file));
    echo "\n";
  }
}

// import beers
import($cb, 'beer-sample/beer/');
import($cb, 'beer-sample/breweries/');
