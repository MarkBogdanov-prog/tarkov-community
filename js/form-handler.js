// Файл: js/form-handler.js
// Обработка формы с мгновенным показом "Форма отправлена"

document.addEventListener('DOMContentLoaded', function() {
    // ===== ЭЛЕМЕНТЫ ФОРМЫ =====
    const proposalForm = document.getElementById('weaponProposalForm');
    const submitBtn = document.getElementById('submitProposal');
    const formResult = document.getElementById('formResult');
    const charCount = document.getElementById('charCount');
    const descriptionInput = document.getElementById('weaponDescription');
    const ideasCountElement = document.getElementById('ideasCount');
    
    // Ключ для сохранения данных формы
    const STORAGE_KEY = 'tarkov_proposal_draft';
    
    // ===== СОХРАНЕНИЕ ЧЕРНОВИКА ФОРМЫ =====
    function saveFormDraft() {
        const formData = {
            weaponName: document.getElementById('weaponName').value,
            weaponType: document.getElementById('weaponType').value,
            weaponCaliber: document.getElementById('weaponCaliber').value,
            weaponCountry: document.getElementById('weaponCountry').value,
            weaponDescription: document.getElementById('weaponDescription').value,
            weaponReason: document.getElementById('weaponReason').value,
            userEmail: document.getElementById('userEmail').value,
            userNickname: document.getElementById('userNickname').value,
            agreeTerms: document.getElementById('agreeTerms').checked
        };
        
        localStorage.setItem(STORAGE_KEY, JSON.stringify(formData));
    }
    
    // ===== ВОССТАНОВЛЕНИЕ ЧЕРНОВИКА =====
    function restoreFormDraft() {
        const savedData = localStorage.getItem(STORAGE_KEY);
        
        if (savedData) {
            const formData = JSON.parse(savedData);
            
            document.getElementById('weaponName').value = formData.weaponName || '';
            document.getElementById('weaponType').value = formData.weaponType || '';
            document.getElementById('weaponCaliber').value = formData.weaponCaliber || '';
            document.getElementById('weaponCountry').value = formData.weaponCountry || '';
            document.getElementById('weaponDescription').value = formData.weaponDescription || '';
            document.getElementById('weaponReason').value = formData.weaponReason || '';
            document.getElementById('userEmail').value = formData.userEmail || '';
            document.getElementById('userNickname').value = formData.userNickname || '';
            document.getElementById('agreeTerms').checked = formData.agreeTerms || false;
            
            // Обновить счетчик символов
            if (descriptionInput && charCount) {
                const count = formData.weaponDescription ? formData.weaponDescription.length : 0;
                charCount.textContent = count;
                updateCharCountColor(count);
            }
        }
    }
    
    // ===== СЧЕТЧИК СИМВОЛОВ =====
    function updateCharCountColor(count) {
        if (!charCount) return;
        
        if (count > 500) {
            charCount.style.color = '#ff0000';
        } else if (count > 400) {
            charCount.style.color = '#ff6b00';
        } else {
            charCount.style.color = '#00ff88';
        }
    }
    
    // Обновление счетчика символов при вводе
    if (descriptionInput && charCount) {
        descriptionInput.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            updateCharCountColor(count);
            saveFormDraft(); // Сохраняем черновик
        });
    }
    
    // Сохранение данных при изменении полей формы
    if (proposalForm) {
        const formElements = proposalForm.querySelectorAll('input, select, textarea');
        formElements.forEach(element => {
            element.addEventListener('input', saveFormDraft);
            element.addEventListener('change', saveFormDraft);
        });
    }
    
    // ===== ВАЛИДАЦИЯ ФОРМЫ =====
    function validateForm() {
        const name = document.getElementById('weaponName').value.trim();
        const type = document.getElementById('weaponType').value;
        const caliber = document.getElementById('weaponCaliber').value.trim();
        const description = document.getElementById('weaponDescription').value.trim();
        const reason = document.getElementById('weaponReason').value.trim();
        const email = document.getElementById('userEmail').value.trim();
        const agree = document.getElementById('agreeTerms').checked;
        
        // Проверка обязательных полей
        if (!name || !type || !caliber || !description || !reason || !email || !agree) {
            showFormResult('error', 'Заполните все обязательные поля');
            return false;
        }
        
        // Проверка длины описания
        if (description.length > 500) {
            showFormResult('error', 'Описание не должно превышать 500 символов');
            return false;
        }
        
        // Проверка email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showFormResult('error', 'Введите корректный email адрес');
            return false;
        }
        
        return true;
    }
    
    // ===== ПОКАЗ РЕЗУЛЬТАТА ОТПРАВКИ =====
    function showFormResult(type, message) {
        if (!formResult) return;
        
        formResult.className = `form-result ${type}`;
        formResult.innerHTML = `
            <div class="result-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        formResult.style.display = 'block';
        formResult.style.opacity = '1';
        
        // Прокрутить к результату
        setTimeout(() => {
            formResult.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }, 100);
        
        // Автоскрытие только для ошибок
        if (type === 'error') {
            setTimeout(() => {
                formResult.style.opacity = '0';
                setTimeout(() => {
                    formResult.style.display = 'none';
                    formResult.style.opacity = '1';
                }, 300);
            }, 5000);
        }
    }
    
    // ===== ОБНОВЛЕНИЕ СЧЕТЧИКА ИДЕЙ =====
    function updateIdeasCount() {
        const proposals = JSON.parse(localStorage.getItem('tarkov_proposals') || '[]');
        const ideasCount = proposals.length || 0;
        
        if (ideasCountElement) {
            // Анимация счетчика
            let current = parseInt(ideasCountElement.textContent) || 0;
            const target = current + 1;
            
            const timer = setInterval(() => {
                current++;
                ideasCountElement.textContent = current;
                
                if (current >= target) {
                    clearInterval(timer);
                }
            }, 30);
        }
    }
    
    // ===== ОТПРАВКА ФОРМЫ =====
    if (proposalForm && submitBtn) {
        proposalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Валидация формы
            if (!validateForm()) {
                return;
            }
            
            // Показать состояние загрузки
            submitBtn.disabled = true;
            submitBtn.classList.add('loading');
            
            // 1. НЕМЕДЛЕННО показываем "Форма отправлена"
            showFormResult('success', '✅ Форма успешно отправлена!');
            
            // 2. Сохраняем предложение в историю
            const proposals = JSON.parse(localStorage.getItem('tarkov_proposals') || '[]');
            const newProposal = {
                weaponName: document.getElementById('weaponName').value,
                weaponType: document.getElementById('weaponType').value,
                timestamp: new Date().toISOString()
            };
            proposals.push(newProposal);
            localStorage.setItem('tarkov_proposals', JSON.stringify(proposals));
            
            // 3. Обновляем счетчик идей
            updateIdeasCount();
            
            // 4. Удаляем черновик
            localStorage.removeItem(STORAGE_KEY);
            
            // 5. Очищаем форму через 1 секунду
            setTimeout(() => {
                proposalForm.reset();
                if (charCount) {
                    charCount.textContent = '0';
                    charCount.style.color = '#00ff88';
                }
            }, 1000);
            
            // 6. Отправляем форму на FormSubmit в фоне
            setTimeout(() => {
                // Создаем временную копию формы для отправки
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = 'https://formsubmit.co/cc924190323f64071d3d73d9f5bf1d3c6';
                tempForm.style.display = 'none';
                
                // Добавляем все поля из оригинальной формы
                const formData = new FormData(proposalForm);
                for (let [name, value] of formData.entries()) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    tempForm.appendChild(input);
                }
                
                // Добавляем скрытые поля FormSubmit
                const hiddenFields = ['_captcha', '_subject', '_template', '_next', '_autoresponse'];
                hiddenFields.forEach(field => {
                    const input = document.querySelector(`input[name="${field}"]`);
                    if (input && input.value) {
                        const clone = input.cloneNode(true);
                        clone.style.display = 'none';
                        tempForm.appendChild(clone);
                    }
                });
                
                // Добавляем в документ и отправляем
                document.body.appendChild(tempForm);
                tempForm.submit();
                
                // Убираем загрузку через 2 секунды
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('loading');
                    
                    // Удаляем временную форму через 5 секунд
                    setTimeout(() => {
                        if (tempForm.parentNode) {
                            tempForm.parentNode.removeChild(tempForm);
                        }
                    }, 5000);
                }, 2000);
                
            }, 500); // Небольшая задержка перед отправкой
            
            console.log('Форма отправлена на FormSubmit');
        });
    }
    
    // ===== ИНИЦИАЛИЗАЦИЯ ПРИ ЗАГРУЗКЕ =====
    function initForm() {
        // Восстановить черновик из LocalStorage
        restoreFormDraft();
        
        // Инициализировать счетчик идей
        const proposals = JSON.parse(localStorage.getItem('tarkov_proposals') || '[]');
        if (ideasCountElement && proposals.length > 0) {
            ideasCountElement.textContent = proposals.length;
        }
        
        // Инициализировать счетчик символов
        if (descriptionInput && charCount) {
            const count = descriptionInput.value.length;
            charCount.textContent = count;
            updateCharCountColor(count);
        }
        
        console.log('Форма обратной связи инициализирована');
    }
    
    // Запуск инициализации
    initForm();
});