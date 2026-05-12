// Ждем загрузки DOM
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== 1. GIF И СЧЕТЧИКИ =====
    const headerGif = document.getElementById('headerGif');
    const survivorsCount = document.getElementById('survivorsCount');
    const ideasCount = document.getElementById('ideasCount');
    const proposeBtn = document.getElementById('proposeBtn');
    
    // Анимация счетчиков
    function animateCounter(element, target) {
        if (!element) return;
        
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 20);
    }
    
    // Запуск анимации счетчиков
    setTimeout(() => {
        if (survivorsCount) {
            const survivors = Math.floor(Math.random() * 3500) + 1500;
            animateCounter(survivorsCount, survivors);
        }
        
        if (ideasCount) {
            const proposals = JSON.parse(localStorage.getItem('tarkov_proposals') || '[]');
            const ideas = proposals.length || Math.floor(Math.random() * 250) + 50;
            animateCounter(ideasCount, ideas);
        }
    }, 1000);
    
    // Прокрутка к форме
    if (proposeBtn) {
        proposeBtn.addEventListener('click', function() {
            const formSection = document.getElementById('proposals');
            if (formSection) {
                formSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    }
    
    // Обработка ошибки загрузки GIF
    if (headerGif) {
        headerGif.addEventListener('error', function() {
            console.log('GIF не загрузилась');
            const fallback = document.querySelector('.video-fallback');
            if (fallback) {
                fallback.style.display = 'block';
            }
        });
    }
    
    // ===== 2. МОБИЛЬНОЕ МЕНЮ =====
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileClose = document.getElementById('mobileClose');
    
    // Открытие меню
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            if (mobileMenu) mobileMenu.classList.add('active');
            if (mobileOverlay) mobileOverlay.classList.add('active');
            mobileMenuBtn.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Закрытие меню
    function closeMobileMenu() {
        if (mobileMenu) mobileMenu.classList.remove('active');
        if (mobileOverlay) mobileOverlay.classList.remove('active');
        if (mobileMenuBtn) mobileMenuBtn.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    if (mobileClose) {
        mobileClose.addEventListener('click', closeMobileMenu);
    }
    
    if (mobileOverlay) {
        mobileOverlay.addEventListener('click', closeMobileMenu);
    }
    
    // Закрытие при клике на ссылку
    document.querySelectorAll('.mobile-nav a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 900) {
                closeMobileMenu();
            }
        });
    });
    
    console.log('Tarkov Community сайт загружен!');
});