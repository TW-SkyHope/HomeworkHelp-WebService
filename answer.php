<?php
header('Content-Type: text/html; charset=utf-8');
require_once("db.php");
require "php/mysql.php";
$db = new MySQLiPDO($pdo);

$id = $_GET['id'] ?? '';
$answerText = $db->findOne('subject', ['id' => $id]);

$imagePath = 'uploads/' . $id . '.jpg';
$hasImage = file_exists($imagePath);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>题目解析结果</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #4caf50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: 'Noto Sans SC', sans-serif;
            line-height: 1.6;
            background: #f5f7fa;
            color: var(--dark-color);
            min-height: 100vh;
            padding: 1rem;
        }
        
        .container {
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
            margin-bottom: 5rem;
        }
        
        .header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 1.2rem;
            position: relative;
            overflow: hidden;
        }
        
        .header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
        }
        
        h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            position: relative;
            z-index: 1;
        }
        
        .subtitle {
            font-size: 0.8rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 1.2rem;
        }

        .question-image {
            width: 100%;
            max-width: 300px;
            margin: 0 auto 1rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #eee;
        }
        
        .question-image img {
            width: 100%;
            display: block;
        }
        
        .answer-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.2rem;
            margin-bottom: 1.2rem;
            border-left: 4px solid var(--accent-color);
        }
        
        .answer-content {
            white-space: pre-wrap;
            font-size: 1rem;
            line-height: 1.7;
            color: var(--dark-color);
            word-break: break-word;
        }
        
        .meta-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.2rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: #666;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.7rem 1rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(67, 97, 238, 0.3);
            flex: 1;
        }
        
        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn i {
            margin-right: 0.3rem;
            font-size: 0.9rem;
        }
        
        .session-id {
            background: #f0f4ff;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-family: monospace;
            color: var(--primary-color);
            word-break: break-all;
        }
        
        .floating-btn {
            position: fixed;
            bottom: 1rem;
            right: 1rem;
            z-index: 100;
            background: var(--success-color);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
            transition: var(--transition);
        }
        
        .floating-btn:active {
            transform: scale(0.95);
        }
        
        .floating-btn i {
            color: white;
            font-size: 1.2rem;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .answer-card {
            animation: fadeIn 0.6s ease forwards;
        }
        
        .btn:active, .answer-card:active {
            transition: none;
            transform: scale(0.98);
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>题目解析结果</h1>
            <p class="subtitle">SkyHope解题系统为您提供详细解答</p>
        </div>
        
        <div class="content">
            
            
            <div class="answer-card">
                <div class="answer-content"><?= $answerText["string"] ?></div>
            </div>
            <?php if ($hasImage): ?>
            <div class="question-image">
                <img src="<?= $imagePath ?>" alt="题目图片">
            </div>
            <?php endif; ?>
            <div class="meta-info">
                <span>会话ID: <span class="session-id"><?= $id ?></span></span>
                
                <div class="action-buttons">
                    <button id="copyBtn" class="btn">
                        <i class="fas fa-copy"></i> 复制
                    </button>
                    <a href="index.php" class="btn">
                        <i class="fas fa-arrow-left"></i> 返回
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <a href="index.php" class="floating-btn">
        <i class="fas fa-home"></i>
    </a>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyBtn = document.getElementById('copyBtn');
            copyBtn.addEventListener('click', function() {
                const text = document.querySelector('.answer-content').textContent;
                navigator.clipboard.writeText(text).then(() => {
                    const originalText = copyBtn.innerHTML;
                    copyBtn.innerHTML = '<i class="fas fa-check"></i> 已复制';
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                    }, 2000);
                }).catch(err => {
                    copyBtn.innerHTML = '<i class="fas fa-times"></i> 失败';
                    setTimeout(() => {
                        copyBtn.innerHTML = originalText;
                    }, 2000);
                });
            });
            
            const answerCard = document.querySelector('.answer-card');
            let pressTimer;
            
            answerCard.addEventListener('touchstart', function(e) {
                pressTimer = setTimeout(() => {
                    const range = document.createRange();
                    range.selectNode(this.querySelector('.answer-content'));
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    e.preventDefault();
                }, 500);
            });
            
            answerCard.addEventListener('touchend', function() {
                clearTimeout(pressTimer);
            });
            
            answerCard.addEventListener('touchmove', function() {
                clearTimeout(pressTimer);
            });
        });
    </script>
</body>
</html>