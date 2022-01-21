<?php
require_once __DIR__ . '/vendor/autoload.php';

const DOMAIN            = 'http://api.seowizard.ru/xmlrpc/?v=extended';
const METHOD_LOGIN      = 'seowizard.loginpass';
const METHOD_PROJ_LIST  = 'seowizard.project_list';
const OUTPUT_FILE       = 'all_projects.txt';

fwrite(STDOUT, "Enter your login: ");
$login = trim(fgets(STDIN));
fwrite(STDOUT, "Enter your token: ");
$token = trim(fgets(STDIN));

$ch = curl_init(DOMAIN);
curl_setopt_array($ch, [
    CURLOPT_HEADER => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: text/xml'
    ],
    CURLOPT_POSTFIELDS => xmlrpc_encode_request(METHOD_LOGIN, [
        $login,
        $token
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => 'cookies.txt',
    CURLOPT_COOKIEFILE => 'cookies.txt',
]);

$response = xmlrpc_decode(curl_exec($ch));
if (is_array($response) && xmlrpc_is_fault($response)) {
    fwrite(STDOUT, $response['faultString'] . PHP_EOL);
} else {
    $projectListRequest = xmlrpc_encode_request(METHOD_PROJ_LIST, null);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $projectListRequest);
    $response = curl_exec($ch);

    $projectList = xmlrpc_decode($response, 'UTF-8');
    if (xmlrpc_is_fault($projectList)) {
        fwrite(STDOUT, $projectList['faultString'] . PHP_EOL);
    } else {
        $resFile = fopen(OUTPUT_FILE, 'w+');
        foreach ($projectList as $project) {
            $content = $project['domain'] . ' - ' . $project['sum_budget'];
            fwrite($resFile, $content . PHP_EOL);
        }
        fclose($resFile);

        fwrite(STDOUT, "Budgets successfully fetched to " . OUTPUT_FILE . PHP_EOL);
    }
}
