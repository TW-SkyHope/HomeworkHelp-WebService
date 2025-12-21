# SkyHope智能解题系统

## 项目简介
SkyHope智能解题系统是一个基于Web的智能解题平台，提供高效的解题服务和管理功能。

## 环境要求
- 任意http服务器(如Apache，Nginx)，需开启Https
- Html+Css+Js支持
- PHP 8.0+
- MySQL 8.0+
- 需联网从CDN获取部分Css样式图标等
- Python 3.0+ (可选，也可直接exe运行，用于运行TxYuanbao-To-PyAPI)

## 安装步骤
1. 确保配置好上述环境，HTTP服务器指定项目根目录，设定伪静态如下
```
    location / {
        rewrite ^/(.*)$ /index.php last;
    }
```
2. 运行TxYuanbao-To-PyAPI内程序，具体方法见 [TxYuanbao-To-PyAPI](https://github.com/TW-SkyHope/TxYuanbao-To-PyAPI)
3. 确保TxYuanbao-To-PyAPI正常运行(默认8000端口，若有需要请更改tack.php中127.0.0.1:8000与aiapi.py最下方端口)
4. 访问 `install.php` 文件，按照安装向导完成配置
5. 安装完成后，点击"进入控制面板"按钮跳转至系统首页

## 使用说明
- 安装成功后，系统会自动创建管理员账号：用户名admin，密码admin123
- 登录后可进入系统后台进行配置和管理
- 系统支持Web端和手机端访问

## 项目结构
```
├── css/              # 样式文件目录
│   ├── -bootstrap-icons.css
│   ├── -cropper.css
│   ├── phstyles.css
│   ├── styles.css
│   └── subject.css
├── js/               # JavaScript文件目录
│   ├── -cropper.js
│   ├── phscript.js
│   ├── script.js
│   └── subject.js
├── php/              # PHP后端文件目录
│   ├── functions.php
│   └── mysql.php
├── SkyHope.jpg       # 项目图片
├── answer.php        # 答案页面
├── index.php         # 主页面
├── install.php       # 安装页面
├── phone.php         # 手机端页面
├── subject.php       # 题目页面
├── tack.php          # API调用相关
└── windows.php       # 窗口页面
```

## 项目维护

若您正在使用我的项目对我的项目有新的需求或发现bug请向于本项目内报告，一般3-7天内会给出答复，后期可能会视作品小星星der数量增加更多功能！

## 作者的话：上次省赛的作品，凑活看吧
