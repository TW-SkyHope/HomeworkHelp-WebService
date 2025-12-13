<?php

function handlePhotoUpload() {
    header('Content-Type: application/json');
    
    $uploadDir = 'uploads/';
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['photo'];
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => '上传错误: ' . $file['error']]);
        exit;
    }
    if ($file['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => '文件太大，最大5MB']);
        exit;
    }
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => '只允许上传图片文件']);
        exit;
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        echo json_encode([
            'success' => true,
            'message' => '上传成功',
            'path' => $targetPath,
            'id' => uniqid(),
            'text' => '这是从图片中识别的题目文本...'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '文件保存失败']);
    }
    exit;
}

?>