<?php
include 'php/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    handleSearchRequest();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    handlePhotoUpload();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkyHope解题 - 欢迎使用</title>
    <link rel="stylesheet" href="css/-bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    <circle cx="12" cy="8" r="1"></circle>
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="16" r="1"></circle>
                </svg>
                <span>SkyHope解题</span>
            </div>
            <nav>
                <ul>
                    <li><a href="index.php" class="active"><i class="bi bi-house-door"></i> 首页</a></li>
                    <li><a href="subject.php"><i class="bi bi-book"></i> 题库</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <h1>SkyHope解题，你值得拥有</h1>
            <p>HTML+CSS+JS+PHP开发,采用MYSQL数据库</p>
            
            <div class="search-box">
                <input type="text" id="search-input" placeholder="输入题目或知识点...">
                <button id="search-btn"><i class="bi bi-search"></i> 搜索</button>
                <button id="voice-btn"><i class="bi bi-mic"></i> 语音搜题</button>
            </div>
        </div>
    </section>

    <div id="camera-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>拍照搜题</h3>
                <button id="close-camera" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="camera-container">
                    <video id="camera-view" autoplay playsinline></video>
                    <canvas id="camera-canvas"></canvas>
                </div>
                <div class="camera-controls">
                    <button id="capture-btn" class="btn btn-primary">
                        <i class="bi bi-camera"></i> 拍照
                    </button>
                    <button id="switch-camera" class="btn btn-outline">
                        <i class="bi bi-arrow-repeat"></i> 切换摄像头
                    </button>
                </div>
                <div id="preview-container">
                    <img id="photo-preview" src="" alt="拍照预览">
                    <div class="preview-controls">
                        <button id="retake-btn" class="btn btn-outline">
                            <i class="bi bi-arrow-counterclockwise"></i> 重拍
                        </button>
                        <button id="upload-btn" class="btn btn-primary">
                            <i class="bi bi-upload"></i> 上传搜题
                        </button>
                    </div>
                </div>
                <div id="upload-status">
                    <div class="spinner"></div>
                    <p>正在上传并识别题目...</p>
                </div>
            </div>
        </div>
    </div>


    <section class="subject-nav">
        <div class="container">
            <h2>覆盖学科</h2>
            <div class="subject-grid">
                <?php
                //物理化学找不到标了
                $subjects = [
                    ['数学', 'bi-calculator', '#4e8cff'],
                    ['语文', 'bi-book', '#ff6b6b'],
                    ['英语', 'bi-translate', '#ffb347'],
                    ['生物', 'bi-flower2', '#4cd964'],
                    ['历史', 'bi-hourglass', '#ff7b7b'],
                    ['物理', '', ''],
                    ['地理', 'bi-globe2', '#5ac8fa'],
                    ['政治', 'bi-bank', '#ff9500'],
                    ['信息', 'bi-lightbulb', '#ffcc5c'],
                    ['化学', '', ''],
                ];
                
                foreach ($subjects as $subject) {
                    echo '<div class="subject-card" style="--subject-color: '.$subject[2].'">';
                    echo '<i class="bi '.$subject[1].'"></i>';
                    echo '<span>'.$subject[0].'</span>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <section class="popular-content">
        <div class="container">
            <div class="section-header">
                <h2>关于此项目(咳咳，本来想随机调用题库的，然后发现没时间了)</h2>
            </div>
            
            <div class="content-grid">
                <div class="content-card">
                    <div class="card-header">
                        <span class="badge">高性能架构(啊我直接抄install内容)</span>
                        <h3>基于最新技术栈构建。</h3>
                    </div>
                    <div class="card-body">
                        <p>提供卓越的性能和响应速度，轻松应对高并发场景。</p>
                    </div>
                    <div class="card-footer">
                        <span><i class="bi bi-eye"></i></span>
                        <span><i class="bi bi-hand-thumbs-up"></i></span>
                    </div>
                </div>
                
                <div class="content-card">
                    <div class="card-header">
                        <span class="badge">企业级安全(实际就是屎山懒得删调试了)</span>
                        <h3>内置多重安全防护机制</h3>
                    </div>
                    <div class="card-body">
                        <p>包括数据加密和严格的访问控制(防护倒是有的,一堆限制_(:з」∠)_)</p>
                    </div>
                    <div class="card-footer">
                        <span><i class="bi bi-eye"></i></span>
                        <span><i class="bi bi-hand-thumbs-up"></i></span>
                    </div>
                </div>
                
                <div class="content-card">
                    <div class="card-header">
                        <span class="badge">模块化设计</span>
                        <h3>采用模块化架构</h3>
                    </div>
                    <div class="card-body">
                        <p>方便扩展和定制，满足您的各种业务需求(模块化是真的，不过耦合性超强滴)</p>
                    </div>
                    <div class="card-footer">
                        <span><i class="bi bi-eye"></i></span>
                        <span><i class="bi bi-hand-thumbs-up"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2>我们的特色</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-lightning-charge"></i>
                    </div>
                    <h3>慢速解答</h3>
                    <p>平均10秒返回答案，精准匹配海量题库，解决学习难题(哎嘿)</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-camera"></i>
                    </div>
                    <h3>拍照搜题</h3>
                    <p>对准题目拍照，智能识别文字，即刻获取解析</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-film"></i>
                    </div>
                    <h3>详细解析</h3>
                    <p>AI辅助，智能解析题目，快速通俗易懂</p>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>关于SkyHope</h4>
                    <ul>
                        <li><a href="https://github.com/TW-SkyHope">github链接</a></li>
                        <li><a href="#">114514</a></li>
                        <li><a href="#">10086</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>产品服务</h4>
                    <ul>
                        <li><a href="subject.php">题库中心</a></li>
                        <li><a href="#">原神启动！</a></li>
                        <li><a href="#">哎嘿嘿</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>关注我们(实际没有链接，悲)</h4>
                    <div class="social-links">
                        <a href="#"><i class="bi bi-wechat"></i></a>
                        <a href="#"><i class="bi bi-tiktok"></i></a>
                    </div>
                    <p></p>
                    <p></p>
                </div>
            </div>
            
            <div class="copyright">
                <p>© 2025 SkyHope版权所有 | 提瓦特ICP备12345678号</p>
            </div>
        </div>
    </footer>
                <div id="voice-recording-status">
    <div class="voice-status-content">
        <i class="bi bi-mic-fill"></i>
        <span>录音中...</span>
        <button id="voice-stop-btn" title="停止录音">
            <i class="bi bi-stop-fill"></i>
        </button>
    </div>
</div>
    <script src="js/script.js"></script>
</body>
</html>