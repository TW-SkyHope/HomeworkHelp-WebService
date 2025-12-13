let responseString = '';
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    const voiceBtn = document.getElementById('voice-btn');
    const voiceStopBtn = document.getElementById('voice-stop-btn');
    const voiceStatus = document.getElementById('voice-recording-status');
    let recognition;
    let isRecording = false;

    voiceBtn.addEventListener('click', async () => {
        if (!isRecording) {
            startVoiceRecording();
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
                searchInput.value = transcript;
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
    initScrollAnimations();

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