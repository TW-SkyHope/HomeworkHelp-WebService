document.addEventListener('DOMContentLoaded', function() {

    const questionCards = document.querySelectorAll('.question-card');
    questionCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    if ('IntersectionObserver' in window) {
        const lazyImageObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const lazyImage = entry.target;
                    if (lazyImage.dataset.src) {
                        lazyImage.src = lazyImage.dataset.src;
                    }
                    lazyImageObserver.unobserve(lazyImage);
                }
            });
        });

        const lazyImages = document.querySelectorAll('.question-image img[loading="lazy"]');
        lazyImages.forEach(function(lazyImage) {
            lazyImageObserver.observe(lazyImage);
        });
    }

    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    }


    const pageButtons = document.querySelectorAll('.page-btn:not(:disabled)');
    pageButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.querySelector('.page-btn.active').classList.remove('active');
            this.classList.add('active');
            
            console.log('切换到第', this.textContent, '页');
        });
    });


    questionCards.forEach(card => {
        card.addEventListener('click', function(e) {

            if (!e.target.closest('a')) {
                const viewLink = this.querySelector('.view-answer');
                if (viewLink) {
                    window.location.href = viewLink.href;
                }
            }
        });
    });


    const subjectFilter = document.getElementById('subject');
    if (subjectFilter) {
        subjectFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
    const sortFilter = document.getElementById('sort');
    if (sortFilter) {
        sortFilter.addEventListener('change', function() {
            this.form.submit();
        });
    }
});