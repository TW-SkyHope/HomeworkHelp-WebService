let responseString = '';
let cropper = null;
let isCropping = false;
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const cameraBtn = document.getElementById('camera-btn');
    const galleryBtn = document.getElementById('gallery-btn');
const fileInput = document.getElementById('file-input');
const voiceBtn = document.getElementById('voice-btn');
const voiceStopBtn = document.getElementById('voice-stop-btn');
const voiceStatus = document.getElementById('voice-recording-status');
let recognition;
let isRecording = false;
galleryBtn.addEventListener('click', () => {
    fileInput.click();
});
fileInput.addEventListener('change', (e) => {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        
        reader.onload = (event) => {
            document.querySelector('.camera-container').style.display = 'none';
            
            photoPreview.src = event.target.result;
            previewContainer.style.display = 'block';
            
            const img = new Image();
            img.onload = function() {
                cameraCanvas.width = img.width;
                cameraCanvas.height = img.height;
                const ctx = cameraCanvas.getContext('2d');
                ctx.drawImage(img, 0, 0);
            };
            img.src = event.target.result;
            
            captureBtn.style.display = 'none';
            galleryBtn.style.display = 'none';
            switchCameraBtn.style.display = 'none';
        };
        
        reader.readAsDataURL(e.target.files[0]);
    }
});
voiceBtn.addEventListener('click', async () => {
    if (!isRecording) {
        await startVoiceRecording();
    } else {
        stopVoiceRecording();
    }
});

voiceStopBtn.addEventListener('click', stopVoiceRecording);

async function startVoiceRecording() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'zh-CN';
        recognition.interimResults = false;
        
        recognition.onresult = (event) => {
            const transcript = event.results[0][0].transcript;
            console.log('识别结果:', transcript);
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = transcript;
                const inputEvent = new Event('input', { bubbles: true });
                searchInput.dispatchEvent(inputEvent);
            }
        };
        
        recognition.onerror = (event) => {
            console.error('识别错误:', event.error);
            showToast(`识别失败: ${event.error}`);
            stopVoiceRecording();
        };
        
        recognition.start();
        voiceStatus.style.display = 'flex';
        voiceBtn.innerHTML = '<i class="bi bi-mic-fill"></i> 录音中...';
        isRecording = true;
        
    } catch (err) {
        console.error('麦克风访问失败:', err);
        showToast('无法访问麦克风，请检查权限设置');
    }
}

function stopVoiceRecording() {
    if (recognition) {
        recognition.stop();
    }
    voiceStatus.style.display = 'none';
    voiceBtn.innerHTML = '<i class="bi bi-mic"></i> 语音搜题';
    isRecording = false;
}

    
    searchBtn.addEventListener('click', function() {
        const query = searchInput.value.trim();
        if (query) performSearch(query);
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            const query = searchInput.value.trim();
            if (query) performSearch(query);
        }
    });
    
    let cameraStream = null;
    let currentFacingMode = 'environment';
    const cameraModal = document.getElementById('camera-modal');
    const cameraView = document.getElementById('camera-view');
    const cameraCanvas = document.getElementById('camera-canvas');
    const captureBtn = document.getElementById('capture-btn');
    const switchCameraBtn = document.getElementById('switch-camera');
    const retakeBtn = document.getElementById('retake-btn');
    const uploadBtn = document.getElementById('upload-btn');
    const previewContainer = document.getElementById('preview-container');
    const photoPreview = document.getElementById('photo-preview');
    const uploadStatus = document.getElementById('upload-status');
    const closeCameraBtn = document.getElementById('close-camera');
    const cancelBtn = document.getElementById('cancel-btn');
    const focusIndicator = document.getElementById('focus-indicator');
    initScrollAnimations();

    cancelBtn.addEventListener('click', function() {
        if (cropper) {
        cropper.destroy();
        cropper = null;
        galleryBtn.style.display = 'block';
    }
    
    resetCameraUI();
    
    closeCameraModal();
});

    cameraBtn.addEventListener('click', async function() {
        if (!/Mobile|Android|iPhone|iPad/i.test(navigator.userAgent)) {
            alert('拍照搜题功能建议在手机APP中使用');
            return;
        }
        
        const hasPermission = await checkCameraPermission();
        if (hasPermission) openCameraModal();
    });

    async function checkCameraPermission() {
        try {
            const permissionStatus = await navigator.permissions.query({ name: 'camera' });
            if (permissionStatus.state === 'denied') {
                alert('摄像头权限已被永久拒绝，请前往浏览器设置手动启用');
                return false;
            }
            return true;
        } catch (err) {
            console.warn('权限API不支持，直接尝试访问摄像头');
            return true;
        }
    }

    function openCameraModal() {
        cameraModal.style.display = 'block';
        resetCameraUI();
        startCamera();
    }

    function closeCameraModal() {
        cameraModal.style.display = 'none';
        stopCamera();
        resetCameraUI();
    }

    async function startCamera() {
        try {
        const constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1280 },
                height: { ideal: 720 },
                advanced: []
            },
            audio: false
        };

        if (currentFacingMode === 'environment') {
            constraints.video.advanced.push(
                { focusMode: 'continuous' },
                { focusMode: 'manual' },
                { exposureMode: 'continuous' }
            );
        }

        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        cameraView.srcObject = cameraStream;

        const videoTrack = cameraStream.getVideoTracks()[0];
        const capabilities = videoTrack.getCapabilities();
        console.log('摄像头能力:', {
            facingMode: currentFacingMode,
            focusModes: capabilities.focusMode || [],
            focusDistance: capabilities.focusDistance || 'unsupported',
            exposure: capabilities.exposureCompensation || 'unsupported'
        });

        if (currentFacingMode === 'environment') {
            initializeRearCameraFocus(videoTrack);
        }

    } catch (err) {
        handleCameraError(err);
    }
}

async function initializeRearCameraFocus(videoTrack) {
    try {
        const capabilities = videoTrack.getCapabilities();
        
        if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
            await videoTrack.applyConstraints({
                advanced: [{
                    focusMode: 'continuous',
                    focusDistance: 0.5 
                }]
            });
            
            setTimeout(async () => {
                try {
                    await videoTrack.applyConstraints({
                        advanced: [{
                            focusMode: 'manual',
                            focusDistance: 0.2
                        }]
                    });
                    
                    await new Promise(resolve => setTimeout(resolve, 200));
                    
                    await videoTrack.applyConstraints({
                        advanced: [{
                            focusMode: 'continuous'
                        }]
                    });
                } catch (scanErr) {
                    console.log('对焦扫描失败:', scanErr);
                }
            }, 500);
        }
    } catch (err) {
        console.warn('后置摄像头对焦初始化失败:', err);
    }
}

    function handleTouchFocus(e) {
        e.preventDefault();
        const touch = e.changedTouches[0];
        const clickEvent = new MouseEvent('click', {
            clientX: touch.clientX,
            clientY: touch.clientY,
            bubbles: true
        });
        cameraView.dispatchEvent(clickEvent);
    }

    function checkCameraCapabilities() {
        const videoTrack = cameraStream.getVideoTracks()[0];
        const capabilities = videoTrack.getCapabilities();
        const settings = videoTrack.getSettings();
        
        console.log('相机能力:', {
            focusModes: capabilities.focusMode || [],
            currentFocus: settings.focusMode || 'auto',
            focusDistance: capabilities.focusDistance || null,
            exposure: capabilities.exposureCompensation || null
        });
    }

    function showFocusTutorial() {
        if (!localStorage.getItem('focusTutorialShown')) {
            const isMobile = /Mobile|Android|iPhone|iPad/i.test(navigator.userAgent);
            const msg = isMobile ? 
                '轻点屏幕任意位置可对焦' : 
                '点击画面可手动选择对焦点';
            
            showToast(msg, 3000);
            localStorage.setItem('focusTutorialShown', 'true');
        }
    }

    function stopCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
            cameraView.srcObject = null;
        }
    }

    captureBtn.addEventListener('click', async function() {
         if (!cameraStream) return;
    
    try {
        const videoTrack = cameraStream.getVideoTracks()[0];
        await videoTrack.applyConstraints({
            advanced: [{ focusMode: 'continuous' }]
        });
        
        await new Promise(resolve => setTimeout(resolve, 200));
        
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = cameraView.videoWidth;
        cameraCanvas.height = cameraView.videoHeight;
        context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
        galleryBtn.style.display = 'none';
        const previewUrl = cameraCanvas.toDataURL('image/jpeg');
    photoPreview.onload = function() {
        const container = document.querySelector('.image-container');
        const aspectRatio = cameraCanvas.width / cameraCanvas.height;
        
        if (aspectRatio > 1) {
            photoPreview.style.width = '100%';
            photoPreview.style.height = 'auto';
        } else {
            photoPreview.style.width = 'auto';
            photoPreview.style.height = '100%';
        }
    };
    photoPreview.src = previewUrl;
        previewContainer.style.display = 'block';
        document.querySelector('.camera-container').style.display = 'none';
        
        captureBtn.style.display = 'none';
        switchCameraBtn.style.display = 'none';
        
        isCropping = false;
    } catch (err) {
        console.error('拍照出错:', err);
        const context = cameraCanvas.getContext('2d');
        cameraCanvas.width = cameraView.videoWidth;
        cameraCanvas.height = cameraView.videoHeight;
        context.drawImage(cameraView, 0, 0, cameraCanvas.width, cameraCanvas.height);
        photoPreview.src = cameraCanvas.toDataURL('image/jpeg');
        previewContainer.style.display = 'block';
        document.querySelector('.camera-container').style.display = 'none';
        captureBtn.style.display = 'none';
        switchCameraBtn.style.display = 'none';
        isCropping = false;
    }
});

const cropBtn = document.getElementById('crop-btn');

cropBtn.addEventListener('click', function() {
    if (!isCropping) {
        startCropping();
    } else {
        finishCropping();
    }
});

function startCropping() {
    isCropping = true;
    cropBtn.innerHTML = '<i class="bi bi-check"></i> 完成';
    document.getElementById('retake-btn').style.display = 'none';
    document.getElementById('upload-btn').style.display = 'none';
    
    cropper = new Cropper(photoPreview, {
        aspectRatio: NaN, 
        viewMode: 1, 
        autoCropArea: 0.8, 
        movable: true,
        zoomable: true,
        rotatable: true,
        scalable: true,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
    });
}

function finishCropping() {
    isCropping = false;
    cropBtn.innerHTML = '<i class="bi bi-crop"></i> 裁剪';
    document.getElementById('retake-btn').style.display = 'block';
    document.getElementById('upload-btn').style.display = 'block';
    
    const croppedCanvas = cropper.getCroppedCanvas();
    
    photoPreview.src = croppedCanvas.toDataURL('image/jpeg');
    
    const context = cameraCanvas.getContext('2d');
    cameraCanvas.width = croppedCanvas.width;
    cameraCanvas.height = croppedCanvas.height;
    context.drawImage(croppedCanvas, 0, 0);
    
    cropper.destroy();
    cropper = null;
}
    retakeBtn.addEventListener('click', function() {
        if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    previewContainer.style.display = 'none';
    document.querySelector('.camera-container').style.display = 'block';
    captureBtn.style.display = 'block';
    switchCameraBtn.style.display = 'block';
    galleryBtn.style.display = 'block';
    isCropping = false;
});


    switchCameraBtn.addEventListener('click', function() {
        currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
    stopCamera();
    startCamera();
    
    showToast(`已切换至${currentFacingMode === 'user' ? '前置' : '后置'}摄像头`, 1000);
});

    cameraView.addEventListener('click', async function(e) {
        if (!cameraStream) return;
    
    const rect = cameraView.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    
    showFocusIndicator(e.clientX - rect.left, e.clientY - rect.top);
    
    try {
        const videoTrack = cameraStream.getVideoTracks()[0];
        const capabilities = videoTrack.getCapabilities();
        
        if (currentFacingMode === 'environment') {
            if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
                await videoTrack.applyConstraints({
                    advanced: [{
                        focusMode: 'manual',
                        focusDistance: 0.9,  
                        pointsOfInterest: [{x, y}]
                    }]
                });
                
                await new Promise(resolve => setTimeout(resolve, 100));
                
                await videoTrack.applyConstraints({
                    advanced: [{
                        focusMode: 'manual',
                        focusDistance: 0.2,
                        pointsOfInterest: [{x, y}]
                    }]
                });
                setTimeout(() => {
                    videoTrack.applyConstraints({
                        advanced: [{ focusMode: 'continuous' }]
                    }).catch(console.warn);
                }, 500);
            }
        } else {
            await videoTrack.applyConstraints({
                advanced: [{ focusMode: 'continuous' }]
            });
        }
    } catch (err) {
        console.warn('对焦失败:', err);
        showToast('对焦失败，已恢复自动模式', 1500);
    }
});

    function showFocusIndicator(posX, posY) {
        focusIndicator.style.left = `${posX - 30}px`;
        focusIndicator.style.top = `${posY - 30}px`;
        focusIndicator.classList.add('active');
        
        setTimeout(() => {
            focusIndicator.classList.remove('active');
        }, 3000);
    }


    uploadBtn.addEventListener('click', function() {
        uploadPhoto();
    });


    closeCameraBtn.addEventListener('click', closeCameraModal);

    function resetCameraUI() {
        previewContainer.style.display = 'none';
    document.querySelector('.camera-container').style.display = 'block';
    uploadStatus.style.display = 'none';
    captureBtn.style.display = 'block';
    switchCameraBtn.style.display = 'block';
    focusIndicator.classList.remove('active');
    isCropping = false;

    if (document.getElementById('crop-btn')) {
        document.getElementById('crop-btn').innerHTML = '<i class="bi bi-crop"></i> 裁剪';
        document.getElementById('retake-btn').style.display = 'block';
        document.getElementById('upload-btn').style.display = 'block';
    }

    const context = cameraCanvas.getContext('2d');
    context.clearRect(0, 0, cameraCanvas.width, cameraCanvas.height);
}

    async function uploadPhoto() {
       const imageData = cameraCanvas.toDataURL('image/jpeg').split(',')[1];
        

        previewContainer.style.display = 'none';
        uploadStatus.style.display = 'block';
        
        const requestData = {
            text: "请为我以简短但必要的方式解答题目，其中的数学符号请尽可能为我用文字进行代替(分数的分号用/代替）！：",
            sequence: "new",
            picture: imageData
        };

        try {
            const response = await fetch('tack.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            });
            
            const result = await response.json();
            
            if (result.error) {
                throw new Error(result.error);
            }
            

            const params = new URLSearchParams();
            params.append('id', result.id);
            window.location.href = `answer.php?${params.toString()}`;
            
        } catch (error) {
            console.error('上传失败:', error);
            alert('上传失败: ' + error.message);
        } finally {
            closeCameraModal();
        }
    }


    function performSearch(query) {
        const searchBtn = document.getElementById('search-btn');
        const originalText = searchBtn.innerHTML;
        searchBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> 搜索中...';
        searchBtn.disabled = true;
        
        const requestData = {
            text: "请为我以简短但必要的方式解答题目，其中的数学符号请尽可能为我用文字进行代替(分数的分号用/代替）！："+query,
            sequence: "new",
            picture: ""
        };

        fetch('tack.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.error) {
                throw new Error(result.error);
            }
            
            const params = new URLSearchParams();
            params.append('text', encodeURIComponent(result.text));
            params.append('id', result.id);
            window.location.href = `answer.php?${params.toString()}`;
        })
        .catch(error => {
            console.error('搜索出错:', error);
            alert('操作失败: ' + error.message);
        })
        .finally(() => {
            searchBtn.innerHTML = originalText;
            searchBtn.disabled = false;
        });
    }


    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);


        const cards = document.querySelectorAll('.subject-card, .content-card, .feature-card');
        cards.forEach(card => {
            observer.observe(card);
        });


        animateOnScroll();
    }


    function animateOnScroll() {
        const elements = document.querySelectorAll('.subject-card:not(.animate), .content-card:not(.animate), .feature-card:not(.animate)');
        
        elements.forEach(element => {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight * 0.8;
            if (isVisible) element.classList.add('animate');
        });
    }

    const cards = document.querySelectorAll('.subject-card, .content-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            const color = getComputedStyle(card).getPropertyValue('--subject-color');
            if (color) card.style.boxShadow = `0 10px 20px ${color}40`;
        });
        
        card.addEventListener('mouseleave', function() {
            card.style.boxShadow = 'var(--shadow)';
        });

        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = '';
        });
    });

    document.querySelectorAll('.content-card').forEach(card => {
        card.addEventListener('click', function() {
            alert('即将跳转到内容详情页');
        });
    });

    window.addEventListener('scroll', function() {
        const scrollPosition = window.scrollY;
        const header = document.querySelector('.header');
        
        if (scrollPosition > 100) {
            header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
        } else {
            header.style.boxShadow = 'var(--shadow)';
        }
        
        animateOnScroll();
    });
    

    document.addEventListener('touchmove', function(e) {
        if (e.scale !== 1) e.preventDefault();
    }, { passive: false });

    let lastTouchEnd = 0;
    document.addEventListener('touchend', function(event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) event.preventDefault();
        lastTouchEnd = now;
    }, false);
});

function showToast(message, duration = 2000) {
    const toast = document.createElement('div');
    toast.className = 'toast-message';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

function handleCameraError(err) {
    let errorMsg = '无法访问摄像头';
    
    if (err.name === 'NotAllowedError') {
        errorMsg = '摄像头权限被拒绝，请手动允许权限';
    } else if (err.name === 'NotFoundError') {
        errorMsg = '未检测到可用摄像头';
    } else if (err.name === 'NotReadableError') {
        errorMsg = '摄像头被其他应用占用，请关闭后重试';
    }
    
    alert(errorMsg);
    console.error('摄像头访问失败:', err);
    closeCameraModal();
}