<?php
/*以下是本人写的php操作mysql的库，具体见github https://github.com/TW-SkyHope/Sql-To-PHP */
// 处理AJAX请求的部分必须放在文件最开头
require "php/mysql.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 确保没有输出任何内容之前设置响应头
    ob_start();
    
    // 设置响应头为JSON
    header('Content-Type: application/json');
    
    // 初始化响应数组
    $response = ['success' => false, 'message' => '未知错误'];
    
    try {
        // 测试数据库连接
        if (isset($_POST['test_connection'])) {
            $dsn = "mysql:host=" . $_POST['db_host'] . ";port=" . $_POST['db_port'];
            if (!empty($_POST['db_name'])) {
                $dsn .= ";dbname=" . $_POST['db_name'];
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $_POST['db_username'], $_POST['db_password'], $options);
            
            // 获取MySQL版本信息
            $stmt = $pdo->query("SELECT VERSION() AS version");
            $version = $stmt->fetchColumn();
            
            $response = [
                'success' => true,
                'message' => "成功连接到MySQL服务器，版本: " . $version
            ];
        }
        
        // 保存数据库配置
        if (isset($_POST['save_config'])) {
            // 首先测试数据库连接
            $dsn = "mysql:host=" . $_POST['db_host'] . ";port=" . $_POST['db_port'] . ";dbname=" . $_POST['db_name'];
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $_POST['db_username'], $_POST['db_password'], $options);
            
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($tables)) {
                $hasData = false;
                foreach ($tables as $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    if ($count > 0) {
                        $hasData = true;
                        break;
                    }
                }
                if ($hasData) {
                    throw new Exception("数据库内已有数据，请先删除所有数据后再安装");
                }
            }

$sql = new MySQLiPDO($pdo);

// 定义表结构
$Fields = [

        [
            'name' => 'id',
            'type' => 'VARCHAR',
            'length' => 255,
            'notnull' => true,
            'comment' => 'id',
            'primary' => true
        ],
        [
            'name' => 'string',
            'type' => 'TEXT',
            'length' => 65534,
            'notnull' => false,
            'comment' => '文字'
        ]
       ];


;

// 定义列名顺序
$columns = 
    // Setdates 表的列顺序
    ['id', 'string'];

// 开始事务处理

    
    // 创建 Setdates 表
    if ($sql->createTable('subject', $Fields) === false) {
        throw new Exception("创建表失败: " . $sql->getError());
    };


            
            // 生成配置文件内容
            $configContent = "<?php
// 数据库配置文件 - 由项目初始化向导生成
define('DB_HOST', '" . addslashes($_POST['db_host']) . "');
define('DB_PORT', '" . addslashes($_POST['db_port']) . "');
define('DB_NAME', '" . addslashes($_POST['db_name']) . "');
define('DB_USER', '" . addslashes($_POST['db_username']) . "');
define('DB_PASS', '" . addslashes($_POST['db_password']) . "');

// 创建PDO连接
try {
    \$dsn = \"mysql:host=\" . DB_HOST . \";port=\" . DB_PORT . \";dbname=\" . DB_NAME . \";charset=utf8mb4\";
    \$options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, \$options);
} catch (PDOException \$e) {
    die(\"数据库连接失败: \" . \$e->getMessage());
}
?>";

            // 写入配置文件
            $filePath = "db.php";
            $result = file_put_contents($filePath, $configContent);
            
            if ($result === false) {
                if (file_exists($filePath)) {
                    throw new Exception("无法写入配置文件：文件已存在但不可写");
                } else {
                    throw new Exception("无法写入配置文件：当前目录不可写或没有足够权限");
                }
            }
            
            $response = [
                'success' => true,
                'message' => "数据库配置已成功保存并创建了所有必要的表结构"
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => "数据库错误: " . $e->getMessage()
        ];
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
    
    // 清除任何可能的输出
    ob_end_clean();
    
    // 确保只输出JSON数据
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>项目初始化向导</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --secondary-color: #10b981;
            --accent-color: #3b82f6;
            --text-color: #374151;
            --text-light: #6b7280;
            --bg-color: #f9fafb;
            --card-bg: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 1.5;
            overflow-x: hidden;
            font-size: 16px;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: 2rem auto;
        }

        .init-wizard {
            position: relative;
            width: 100%;
            height: 800px;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            display: flex;
        }

        .sidebar {
            width: 320px;
            background: linear-gradient(135deg, var(--primary-color, #4f46e5), var(--primary-light, #6366f1));
            padding: 50px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            z-index: -1;
        }

        .logo {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 50px;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 15px;
            font-size: 32px;
        }

        .steps {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .step {
            display: flex;
            align-items: center;
            padding: 18px 0;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 18px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .step.active .step-number {
            background-color: white;
            color: var(--primary-color, #4f46e5);
        }

        .step.completed .step-number {
            background-color: var(--secondary-color, #10b981);
            color: white;
        }

        .step.disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        .step-text {
            flex-grow: 1;
        }

        .step-title {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 18px;
        }

        .step-description {
            font-size: 14px;
            opacity: 0.8;
        }

        .content {
            flex-grow: 1;
            padding: 60px;
            overflow-y: auto;
            position: relative;
        }

        .section {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .section.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 32px;
            margin-bottom: 25px;
            color: var(--text-color, #374151);
            font-weight: 700;
        }

        p {
            color: var(--text-light, #6b7280);
            margin-bottom: 35px;
            max-width: 700px;
            font-size: 17px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 28px;
            background-color: var(--primary-color, #4f46e5);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            background-color: var(--primary-light, #6366f1);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn i:not(.document-icon) {
            margin-right: 10px;
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border-color, #e5e7eb);
            color: var(--text-color, #374151);
            box-shadow: none;
        }

        .btn-outline:hover {
            background-color: rgba(0, 0, 0, 0.02);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-group {
            display: flex;
            gap: 20px;
            margin-top: 50px;
        }

        .welcome-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 50px 0;
        }

        .feature-card {
            background-color: var(--card-bg, #ffffff);
            border-radius: 14px;
            padding: 30px;
            border: 1px solid var(--border-color, #e5e7eb);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: white;
            font-size: 24px;
        }

        .feature-icon.blue {
            background-color: var(--accent-color, #3b82f6);
        }

        .feature-icon.green {
            background-color: var(--secondary-color, #10b981);
        }

        .feature-icon.purple {
            background-color: var(--primary-color, #4f46e5);
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 18px;
            color: var(--text-color, #374151);
        }

        .feature-card p {
            font-size: 16px;
            margin-bottom: 0;
            color: var(--text-light, #6b7280);
        }

        .requirements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .requirement {
            background-color: var(--card-bg, #ffffff);
            border-radius: 14px;
            padding: 25px;
            border: 1px solid var(--border-color, #e5e7eb);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .requirement i {
            font-size: 32px;
            margin-bottom: 20px;
            color: var(--accent-color, #3b82f6);
        }

        .requirement h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .requirement p {
            font-size: 15px;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 25px;
            max-width: 800px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-color, #374151);
            font-size: 16px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            background-color: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e5e7eb);
            border-radius: 10px;
            color: var(--text-color, #374151);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-color, #3b82f6);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-hint {
            display: block;
            margin-top: 10px;
            font-size: 14px;
            color: var(--text-light, #6b7280);
        }

        .test-connection-btn {
            margin-top: 15px;
            padding: 10px 16px;
            background-color: var(--accent-color, #3b82f6);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .test-connection-btn:hover {
            background-color: #2563eb;
        }

        .test-connection-btn i {
            margin-right: 8px;
        }

        .connection-status {
            margin-top: 15px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }

        .connection-status.success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color, #10b981);
            border: 1px solid rgba(16, 185, 129, 0.2);
            display: block;
        }

        .connection-status.error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color, #ef4444);
            border: 1px solid rgba(239, 68, 68, 0.2);
            display: block;
        }

        .notification {
            position: fixed;
            top: 30px;
            right: 30px;
            padding: 18px 28px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            box-shadow: var(--shadow-lg);
            transform: translateX(150%);
            transition: transform 0.4s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            font-size: 16px;
            text-decoration: none;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background-color: var(--success-color, #10b981);
        }

        .notification.error {
            background-color: var(--error-color, #ef4444);
        }

        .notification i {
            margin-right: 12px;
        }

        .complete-content {
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            padding: 40px 0;
        }

        .complete-icon {
            font-size: 100px;
            color: var(--secondary-color, #10b981);
            margin-bottom: 40px;
        }

        /* 新增样式 - 书本图标专用 */
        .document-icon {
            margin-right: 10px;
            display: inline-block;
        }

        /* 去除所有下划线 */
        a, a:hover, 
        .btn, .btn:hover, 
        .btn-outline, .btn-outline:hover {
            text-decoration: none !important;
        }

        @media (max-width: 1200px) {
            .init-wizard {
                height: auto;
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                padding: 40px;
            }
            
            .steps {
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 30px;
            }
            
            .step {
                flex-direction: column;
                align-items: center;
                min-width: 180px;
                padding: 0 20px;
                text-align: center;
            }
            
            .step-number {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .content {
                padding: 40px;
            }
        }

        @media (max-width: 768px) {
            .welcome-features, .requirements {
                grid-template-columns: 1fr;
            }
            
            h2 {
                font-size: 28px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .container {
                width: 98%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="init-wizard">
            <!-- 侧边栏导航 -->
            <div class="sidebar">
                <div class="logo">
                    <i class="fas fa-rocket"></i>
                    <span>项目初始化</span>
                </div>
                
                <div class="steps">
                    <div class="step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-text">
                            <div class="step-title">欢迎</div>
                            <div class="step-description">开始您的项目旅程</div>
                        </div>
                    </div>
                    
                    <div class="step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-text">
                            <div class="step-title">系统要求</div>
                            <div class="step-description">检查您的环境</div>
                        </div>
                    </div>
                    
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-text">
                            <div class="step-title">数据库配置</div>
                            <div class="step-description">设置数据库连接</div>
                        </div>
                    </div>
                    
                    <div class="step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-text">
                            <div class="step-title">完成</div>
                            <div class="step-description">准备启动</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 主要内容区域 -->
            <div class="content">
                <!-- 欢迎页面 -->
                <div class="section active" id="welcome-section">
                    <h2>欢迎使用项目初始化向导</h2>
                    <p>感谢您选择我们的平台。这个向导将帮助您快速配置和启动您的项目，只需几个简单步骤即可完成设置。</p>
                    
                    <div class="welcome-features">
                        <div class="feature-card">
                            <div class="feature-icon blue">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3>高性能架构</h3>
                            <p>基于最新技术栈构建，提供卓越的性能和响应速度，轻松应对高并发场景。</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon green">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>企业级安全</h3>
                            <p>内置多重安全防护机制，包括数据加密、CSRF保护和严格的访问控制。</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon purple">
                                <i class="fas fa-cogs"></i>
                            </div>
                            <h3>模块化设计</h3>
                            <p>采用模块化架构，方便扩展和定制，满足您的各种业务需求。</p>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <a href="#" class="btn" id="next-btn">
                            <i class="fas fa-arrow-right"></i> 开始配置
                        </a>
                    </div>
                </div>
                
                <!-- 系统要求页面 -->
                <div class="section" id="requirements-section">
                    <h2>系统要求</h2>
                    <p>在继续之前，请确保您的服务器环境满足以下最低要求。这将确保项目能够正常运行。</p>
                    
                    <div class="requirements">
                        <div class="requirement">
                            <i class="fab fa-php"></i>
                            <h3>PHP 8.0+</h3>
                            <p>需要命名空间、类型声明等现代PHP特性支持。</p>
                        </div>
                        
                        <div class="requirement">
                            <i class="fas fa-database"></i>
                            <h3>MySQL 8.0+</h3>
                            <p>支持JSON字段、窗口函数等高级数据库功能。</p>
                        </div>
                        
                        <div class="requirement">
                            <i class="fas fa-server"></i>
                            <h3>Web服务器</h3>
                            <p>Apache/Nginx，推荐使用最新稳定版本。</p>
                        </div>
                        
                        <div class="requirement">
                            <i class="fas fa-memory"></i>
                            <h3>内存</h3>
                            <p>至少512MB可用内存，推荐1GB以上。</p>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <a href="#" class="btn btn-outline" id="prev-btn">
                            <i class="fas fa-arrow-left"></i> 上一步
                        </a>
                        <a href="#" class="btn" id="next-req-btn">
                            <i class="fas fa-database"></i> 数据库配置
                        </a>
                    </div>
                </div>
                
                <!-- 数据库配置页面 -->
                <div class="section" id="db-config-section">
                    <h2>数据库配置</h2>
                    <p>请填写您的数据库连接信息。这些信息将用于生成配置文件并建立数据库连接。</p>
                    
                    <form id="db-config-form">
                        <div class="form-group">
                            <label for="db-host">数据库主机</label>
                            <input type="text" id="db-host" class="form-control" value="localhost" required>
                            <span class="form-hint">通常是 localhost 或数据库服务器的IP地址</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="db-port">数据库端口</label>
                            <input type="number" id="db-port" class="form-control" value="3306" required>
                            <span class="form-hint">MySQL默认端口是3306</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="db-name">数据库名称</label>
                            <input type="text" id="db-name" class="form-control" required>
                            <span class="form-hint">确保此数据库已存在或您的MySQL用户有创建数据库的权限</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="db-username">数据库用户名</label>
                            <input type="text" id="db-username" class="form-control" required>
                            <span class="form-hint">具有访问指定数据库权限的MySQL用户</span>
                        </div>
                        
                        <div class="form-group">
                            <label for="db-password">数据库密码</label>
                            <input type="password" id="db-password" class="form-control" required>
                            <span class="form-hint">数据库用户的密码</span>
                        </div>
                        
                        <!-- 测试连接按钮和状态显示 -->
                        <button type="button" id="test-connection-btn" class="test-connection-btn">
                            <i class="fas fa-plug"></i> 测试数据库连接
                        </button>
                        
                        <div id="connection-status" class="connection-status"></div>
                        
                        <div class="btn-group">
                            <a href="#" class="btn btn-outline" id="prev-db-btn">
                                <i class="fas fa-arrow-left"></i> 上一步
                            </a>
                            <button type="submit" class="btn">
                                <i class="fas fa-save"></i> 保存配置
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- 完成页面 -->
                <div class="section" id="complete-section">
                    <div class="complete-content">
                        <div class="complete-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        
                        <h2>配置完成!</h2>
                        <p>恭喜您，项目初始化配置已完成。我们已经为您创建了所有必要的数据库表结构，包括管理员账号(用户名:admin, 密码:admin123)和一些默认数据。您现在可以开始使用系统了。如有任何问题，请查阅我们的文档或联系技术支持。</p>
                        
                        <div class="btn-group" style="justify-content: center;">
                            <a href="/" class="btn">
                                <i class="fas fa-home"></i> 进入控制面板
                            </a>
                            <a href="#" class="btn btn-outline">
                                <i class="fas fa-book document-icon"></i> 查看文档
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 通知消息 -->
    <div class="notification success" id="notification">
        <i class="fas fa-check-circle"></i>
        数据库配置已成功保存!
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.section');
    const steps = document.querySelectorAll('.step');
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const nextReqBtn = document.getElementById('next-req-btn');
    const prevDbBtn = document.getElementById('prev-db-btn');
    const dbConfigForm = document.getElementById('db-config-form');
    const testConnectionBtn = document.getElementById('test-connection-btn');
    const connectionStatus = document.getElementById('connection-status');
    const notification = document.getElementById('notification');
    
    let currentSection = 0;
    let isCompleted = false;
    
    // 初始化显示第一个部分
    showSection(currentSection);
    
    // 显示指定步骤
    function showSection(index) {
        // 防止索引越界
        if (index < 0) index = 0;
        if (index >= sections.length) index = sections.length - 1;
        
        // 更新当前步骤
        currentSection = index;
        
        // 更新步骤指示器状态
        updateStepStatus();
        
        // 切换内容区域
        sections.forEach(section => section.classList.remove('active'));
        sections[index].classList.add('active');
    }
    
    // 更新步骤状态
    function updateStepStatus() {
        steps.forEach((step, i) => {
            // 重置所有状态
            step.classList.remove('active', 'completed', 'disabled');
            
            // 标记已完成步骤
            if (i < currentSection) {
                step.classList.add('completed');
            } 
            // 标记当前活动步骤
            else if (i === currentSection) {
                step.classList.add('active');
            }
            
            // 禁用未完成的步骤（除非已经完成所有步骤）
            if (i > currentSection && !isCompleted) {
                step.classList.add('disabled');
            }
        });
    }
    
    // 下一步按钮事件 - 欢迎页面
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(currentSection + 1);
        });
    }
    
    // 上一步按钮事件 - 系统要求页面
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(currentSection - 1);
        });
    }
    
    // 下一步按钮事件 - 系统要求页面
    if (nextReqBtn) {
        nextReqBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(currentSection + 1);
        });
    }
    
    // 上一步按钮事件 - 数据库配置页面
    if (prevDbBtn) {
        prevDbBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showSection(currentSection - 1);
        });
    }
    
    // 测试数据库连接
    if (testConnectionBtn) {
        testConnectionBtn.addEventListener('click', function() {
            const dbHost = document.getElementById('db-host').value;
            const dbPort = document.getElementById('db-port').value;
            const dbName = document.getElementById('db-name').value;
            const dbUsername = document.getElementById('db-username').value;
            const dbPassword = document.getElementById('db-password').value;
            
            if (!dbHost || !dbPort || !dbName || !dbUsername) {
                connectionStatus.textContent = "✗ 请填写所有必填字段";
                connectionStatus.className = "connection-status error";
                connectionStatus.style.display = "block";
                return;
            }
            
            // 显示加载状态
            connectionStatus.textContent = "正在测试数据库连接...";
            connectionStatus.className = "connection-status";
            connectionStatus.style.display = "block";
            
            // 使用URLSearchParams格式化数据
            const formData = new URLSearchParams();
            formData.append('test_connection', '1');
            formData.append('db_host', dbHost);
            formData.append('db_port', dbPort);
            formData.append('db_name', dbName);
            formData.append('db_username', dbUsername);
            formData.append('db_password', dbPassword);
            
            // 发送请求
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('网络响应不正常');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    connectionStatus.textContent = "✓ 数据库连接成功! " + data.message;
                    connectionStatus.className = "connection-status success";
                } else {
                    connectionStatus.textContent = "✗ 连接失败: " + data.message;
                    connectionStatus.className = "connection-status error";
                }
            })
            .catch(error => {
                connectionStatus.textContent = "✗ 请求失败: " + error.message;
                connectionStatus.className = "connection-status error";
                console.error('Error:', error);
            });
        });
    }
    
    // 表单提交
    if (dbConfigForm) {
        dbConfigForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const dbHost = document.getElementById('db-host').value;
            const dbPort = document.getElementById('db-port').value;
            const dbName = document.getElementById('db-name').value;
            const dbUsername = document.getElementById('db-username').value;
            const dbPassword = document.getElementById('db-password').value;
            
            if (!dbHost || !dbPort || !dbName || !dbUsername) {
                notification.textContent = "✗ 请填写所有必填字段";
                notification.className = "notification error";
                notification.classList.add('show');
                
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
                return;
            }
            
            const formData = new URLSearchParams();
            formData.append('save_config', '1');
            formData.append('db_host', dbHost);
            formData.append('db_port', dbPort);
            formData.append('db_name', dbName);
            formData.append('db_username', dbUsername);
            formData.append('db_password', dbPassword);
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('网络响应不正常');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    notification.textContent = "✓ " + data.message;
                    notification.className = "notification success";
                    notification.classList.add('show');
                    
                    isCompleted = true;
                    updateStepStatus();
                    
                    setTimeout(() => {
                        notification.classList.remove('show');
                        showSection(3); // 跳转到完成页面
                    }, 2000);
                } else {
                    notification.textContent = "✗ " + data.message;
                    notification.className = "notification error";
                    notification.classList.add('show');
                    
                    setTimeout(() => {
                        notification.classList.remove('show');
                    }, 3000);
                }
            })
            .catch(error => {
                notification.textContent = "✗ 保存失败: " + error.message;
                notification.className = "notification error";
                notification.classList.add('show');
                console.error('Error:', error);
                
                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            });
        });
    }
    
    // 点击步骤导航
    steps.forEach((step, index) => {
        step.addEventListener('click', function() {
            // 如果步骤不是禁用状态，允许跳转
            if (!step.classList.contains('disabled')) {
                showSection(index);
            }
        });
    });
});
    </script>
</body>
</html>