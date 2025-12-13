<?php
include 'php/functions.php';
require_once("db.php");
require "php/mysql.php";
$db = new MySQLiPDO($pdo);

$imageFiles = glob('uploads/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
usort($imageFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$questions = [];
foreach ($imageFiles as $file) {
    $filename = pathinfo($file, PATHINFO_FILENAME);
    $questions[] = [
        'id' => $filename,
        'image' => $file,
        'answer' => $db->findOne('subject', ['id' => $filename])["string"],
        'timestamp' => filemtime($file)
    ];
}

$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchTerm = strtolower($_GET['search']);
    foreach ($questions as $question) {
        if (strpos(strtolower($question['id']), $searchTerm) !== false || 
            strpos(strtolower($question['answer']), $searchTerm) !== false) {
            $searchResults[] = $question;
        }
    }
} else {
    $searchResults = $questions;
}

if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'oldest':
            usort($searchResults, function($a, $b) {
                return $a['timestamp'] - $b['timestamp'];
            });
            break;
        case 'id':
            usort($searchResults, function($a, $b) {
                return strcmp($a['id'], $b['id']);
            });
            break;
        default:
            usort($searchResults, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
    }
} else {
    usort($searchResults, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>题库中心 - SkyHope解题</title>
    <link rel="stylesheet" href="css/-bootstrap-icons.css">
    <link rel="stylesheet" href="css/subject.css">
    <style>
        .back-btn {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 100;
            background: #4361ee;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            color: white;
            text-decoration: none;
        }
        
        .header {
            padding-top: 60px;
        }
        .question-image {
            position: relative;
        }
        
        .question-id {
            position: absolute;
            margin: 0 0 5px 0;
            left: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.7rem;
            max-width: 60%;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .question-time {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        
        .pagination {
            display: none;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <main class="subject-container">
        <div class="container">
            <div class="search-section">
                <form method="GET" action="subject.php" class="search-form">
                    <div class="search-box">
                        <input type="text" name="search" placeholder="输入题目ID或关键词..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                        <button type="submit"><i class="bi bi-search"></i> 搜索</button>
                    </div>
                    <div class="search-options">
                        <div class="search-filter">
                            <label for="subject"><i class="bi bi-bookmark"></i> 学科</label>
                            <select id="subject" name="subject">
                                <option value="">全部学科（这个功能还没做ing)</option>
                                <option value="math" <?= isset($_GET['subject']) && $_GET['subject'] === 'math' ? 'selected' : '' ?>>数学</option>
                                <option value="chinese" <?= isset($_GET['subject']) && $_GET['subject'] === 'chinese' ? 'selected' : '' ?>>语文</option>
                                <option value="english" <?= isset($_GET['subject']) && $_GET['subject'] === 'english' ? 'selected' : '' ?>>英语</option>
                                <option value="physics" <?= isset($_GET['subject']) && $_GET['subject'] === 'physics' ? 'selected' : '' ?>>物理</option>
                                <option value="chemistry" <?= isset($_GET['subject']) && $_GET['subject'] === 'chemistry' ? 'selected' : '' ?>>化学</option>
                            </select>
                        </div>
                        <div class="search-filter">
                            <label for="sort"><i class="bi bi-filter"></i> 排序（这个功能还没做ing)</label>
                            <select id="sort" name="sort">
                                <option value="newest" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'newest') ? 'selected' : '' ?>>最新添加</option>
                                <option value="oldest" <?= isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'selected' : '' ?>>最早添加</option>
                                <option value="id" <?= isset($_GET['sort']) && $_GET['sort'] === 'id' ? 'selected' : '' ?>>ID排序</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="question-grid">
                <?php if (empty($searchResults)): ?>
                    <div class="no-results">
                        <i class="bi bi-search"></i>
                        <h3>没有找到相关题目</h3>
                        <p>尝试使用不同的关键词搜索</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($searchResults as $question): ?>
                        <div class="question-card" data-id="<?= $question['id'] ?>">
                            <div class="question-image">
                                <img src="<?= $question['image'] ?>" alt="题目图片" loading="lazy">
                                <div class="question-id" >ID: <?= $question['id'] ?></div>
                                <div class="question-time" title="添加时间">
                                    <?= date('Y-m-d H:i', $question['timestamp']) ?>
                                </div>
                            </div>
                            <div class="question-preview">
                                <p><?= mb_substr($question['answer'], 0, 50) ?>...</p>
                                <a href="answer.php?id=<?= $question['id'] ?>" class="view-answer">
                                    <i class="bi bi-eye"></i> 查看解析
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="subject.js"></script>
    <script>
        document.getElementById('sort').addEventListener('change', function() {
            const form = document.querySelector('.search-form');
            form.submit();
        });
        
        document.getElementById('subject').addEventListener('change', function() {
            const form = document.querySelector('.search-form');
            form.submit();
        });
    </script>
</body>
</html>