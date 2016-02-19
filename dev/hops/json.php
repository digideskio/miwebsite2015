<?php

$nameTmpFile = 'modules_dump.tmp.json';

if(!file_exists($nameTmpFile)) {
    include 'hops_modules.php';

    $hopsModules = new HOPSModules();

    $modules = $hopsModules->getModules();

    file_put_contents($nameTmpFile, json_encode($modules, JSON_PRETTY_PRINT));
}

$fileContents = file_get_contents($nameTmpFile);

header('Content-Type', 'application/json');
header('Content-Length', count($fileContents));

echo $fileContents;
