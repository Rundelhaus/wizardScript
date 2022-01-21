<?php
require_once __DIR__.'/vendor/autoload.php';

const DOMAIN = 'http://api.seowizard.ru/xmlrpc/?v=extended';
$login = '';
$token = '';

$ch = curl_init(DOMAIN);
curl_setopt_array($ch, [
    CURLOPT_HEADER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: text/xml'
    ],
    CURLOPT_POSTFIELDS => xmlrpc_encode_request('seowizard.loginpass', [
        $login,
        $token
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => 'cookies.txt',
    CURLOPT_COOKIEFILE => 'cookies.txt',
]);

curl_exec($ch);
$projectListRequest = xmlrpc_encode_request('seowizard.project_list', null);

curl_setopt($ch, CURLOPT_POSTFIELDS, $projectListRequest);

$ret = curl_exec($ch);
$projectList = xmlrpc_decode($ret, 'UTF-8');

$resFile = fopen('all_projects.txt', 'w+');

foreach ($projectList as $project) {
    $content = $project['domain'] . ' - ' . $project['sum_budget'];
    fwrite($resFile, $content . PHP_EOL);
}
fclose($resFile);
