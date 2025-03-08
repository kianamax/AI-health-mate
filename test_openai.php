<?php
require_once 'config.php';
$apiKey = OPENAI_API_KEY;
$url = "https://api.openai.com/v1/completions";
$data = [
    "model" => "text-davinci-003",
    "prompt" => "Hello, test this API key.",
    "max_tokens" => 50
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_error($ch)) {
    echo "Error: " . curl_error($ch);
} else {
    $result = json_decode($response, true);
    echo "Response: " . print_r($result, true);
}
curl_close($ch);
?>