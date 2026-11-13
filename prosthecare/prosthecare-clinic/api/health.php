<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'php_version' => PHP_VERSION,
    'time' => date('c'),
    'cwd' => getcwd(),
    'files_in_parent' => @scandir(dirname(__DIR__)) ?: [],
    'files_in_api' => @scandir(__DIR__) ?: []
]);
