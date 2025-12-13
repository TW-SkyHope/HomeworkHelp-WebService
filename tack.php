<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";
require "php/mysql.php";
$db = new MySQLiPDO($pdo);

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'error' => 'JSON格式错误: ' . json_last_error_msg(),
        'id' => uniqid()
    ]);
    exit;
}
if (!is_array($data) || !isset($data['sequence']) || $data['sequence'] !== 'new') {
    echo json_encode([
        'error' => '无效请求参数',
        'required_fields' => [
            'text' => 'string (可为空)',
            'sequence' => '必须为"new"',
            'picture' => 'string (Base64图片数据，可为空)'
        ],
        'id' => uniqid()
    ]);
    exit;
}
$forwardData = $json;
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://127.0.0.1:8000/hunyuan',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $forwardData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Content-Length: ' . strlen($forwardData)
    ],
    CURLOPT_TIMEOUT => 10086,
    CURLOPT_CONNECTTIMEOUT => 5
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($curlError || $httpCode !== 200) {
    $errorInfo = [
        'timestamp' => date('Y-m-d H:i:s'),
        'backend_url' => 'http://127.0.0.1:8000/hunyuan',
        'http_status' => $httpCode,
        'curl_error' => $curlError,
        'response_sample' => substr($response, 0, 200)
    ];
    
    error_log("Backend Error: " . print_r($errorInfo, true));
    
    echo json_encode([
        'error' => $curlError ?: "后端服务异常 (HTTP {$httpCode})",
        'id' => uniqid(),
        'debug' => ($httpCode >= 500) ? $errorInfo : null
    ]);
    exit;
}
$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'error' => '后端返回数据格式无效: ' . json_last_error_msg(),
        'id' => uniqid(),
        'raw_response' => substr($response, 0, 200)
    ]);
    exit;
}
$db->insert('subject', [
    'id' => $result["id"],
    'string' => $result["text"]
]);
if (!empty($data['picture'])) {
    $imageData = $data['picture'];
    $imageInfo = getimagesizefromstring(base64_decode($imageData));
    
    if (!file_exists('uploads')) {
        mkdir('uploads', 0755, true);
    }
    
    if ($imageInfo !== false) {
        $extension = '.jpg';
        $filename = 'uploads/' . ($result['id'] ?? uniqid()) . $extension;
        file_put_contents($filename, base64_decode($imageData));
    }
}else{
    $sourceFile = 'SkyHope.jpg';
    $destinationFile = "uploads/".$result['id'] .".jpg";
    copy($sourceFile, $destinationFile);
}
echo json_encode([
    'text' => $result['text'] ?? '',
    'id' => $result['id'] ?? uniqid(),
    'error' => $result['error'] ?? null
], JSON_UNESCAPED_UNICODE);
?>