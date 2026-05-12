// Файл: js/api-handler.js
// Отправка формы через Fetch API

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('weaponProposalForm');
    
    if (!form) return;
    
    // Сохраняем оригинальный action для фолббека
    const originalAction = form.action;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Показываем состояние загрузки
        const submitBtn = document.getElementById('submitProposal');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="loader"></div> ОТПРАВКА...';
        
        // Собираем данные формы
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Получаем сохранённые учётные данные
        const credentials = localStorage.getItem('tarkov_credentials');
        let headers = {
            'Content-Type': 'application/json'
        };
        
        if (credentials) {
            headers['Authorization'] = 'Basic ' + btoa(credentials);
        }
        
        try {
            // Определяем URL в зависимости от наличия авторизации
            const apiUrl = credentials 
                ? '/api/proposals'           // Для авторизованных
                : '/api/users';              // Для новых пользователей
            
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                showMessage('success', result.message);
                
                // Если новый пользователь - сохраняем логин/пароль
                if (result.login && result.password) {
                    localStorage.setItem('tarkov_credentials', 
                        `${result.login}:${result.password}`);
                    
                    // Показываем учётные данные
                    showCredentials(result.login, result.password);
                }
                
                // Если есть profile_url - показываем ссылку
                if (result.profile_url) {
                    showProfileLink(result.profile_url);
                }
                
                form.reset();
            } else {
                showMessage('error', result.error.join ? result.error.join('<br>') : result.error);
            }
            
        } catch (error) {
            showMessage('error', 'Ошибка соединения с сервером');
            console.error('Fetch error:', error);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
    
    function showMessage(type, text) {
        const resultDiv = document.getElementById('formResult');
        resultDiv.className = `form-result ${type}`;
        resultDiv.innerHTML = `
            <div class="result-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${text}</span>
            </div>
        `;
        resultDiv.style.display = 'block';
        
        setTimeout(() => {
            resultDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
    }
    
    function showCredentials(login, password) {
        const resultDiv = document.getElementById('formResult');
        const credDiv = document.createElement('div');
        credDiv.className = 'credentials-box';
        credDiv.innerHTML = `
            <div class="login">🔐 ЛОГИН: <strong>${login}</strong></div>
            <div class="password">🔑 ПАРОЛЬ: <strong>${password}</strong></div>
            <p style="color: #ff6b00; margin-top: 10px;">⚠️ Сохраните эти данные!</p>
        `;
        resultDiv.appendChild(credDiv);
    }
    
    function showProfileLink(url) {
        const resultDiv = document.getElementById('formResult');
        const linkDiv = document.createElement('div');
        linkDiv.style.marginTop = '15px';
        linkDiv.innerHTML = `
            <a href="${url}" class="btn btn-primary" style="display: inline-block; padding: 10px 20px;">
                <i class="fas fa-user"></i> ПЕРЕЙТИ В ПРОФИЛЬ
            </a>
        `;
        resultDiv.appendChild(linkDiv);
    }
});