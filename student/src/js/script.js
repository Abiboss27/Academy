document.addEventListener('DOMContentLoaded', function() {
    // Подтверждение удаления
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот элемент?')) {
                e.preventDefault();
            }
        });
    });
    
    // Переключение цены для бесплатного курса
    const freeCourseCheckbox = document.getElementById('is_free_course');
    if (freeCourseCheckbox) {
        freeCourseCheckbox.addEventListener('change', function() {
            const priceField = document.getElementById('price');
            const discountedPriceField = document.getElementById('discounted_price');
            
            if (this.checked) {
                priceField.disabled = true;
                discountedPriceField.disabled = true;
                priceField.value = '0';
                discountedPriceField.value = '0';
            } else {
                priceField.disabled = false;
                discountedPriceField.disabled = false;
            }
        });
    }
    
    // Валидация формы курса
    const courseForm = document.querySelector('.course-form');
    if (courseForm) {
        courseForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const shortDescription = document.getElementById('short_description');
            
            if (title.value.trim() === '') {
                alert('Название курса обязательно для заполнения');
                title.focus();
                e.preventDefault();
                return;
            }
            
            if (shortDescription.value.trim() === '') {
                alert('Краткое описание обязательно для заполнения');
                shortDescription.focus();
                e.preventDefault();
                return;
            }
        });
    }
    
    // Валидация формы урока
    const lessonForm = document.querySelector('.lesson-form');
    if (lessonForm) {
        lessonForm.addEventListener('submit', function(e) {
            const title = document.getElementById('title');
            const lessonType = document.getElementById('lesson_type').value;
            
            if (title.value.trim() === '') {
                alert('Название урока обязательно для заполнения');
                title.focus();
                e.preventDefault();
                return;
            }
            
            if (lessonType === 'video') {
                const videoType = document.getElementById('video_type').value;
                const videoUrl = document.getElementById('video_url');
                const videoFile = document.getElementById('video_file');
                
                if (videoType === 'youtube' || videoType === 'vimeo') {
                    if (videoUrl.value.trim() === '') {
                        alert('URL видео обязательно для заполнения');
                        videoUrl.focus();
                        e.preventDefault();
                        return;
                    }
                } else if (videoType === 'file') {
                    if (videoFile.files.length === 0) {
                        alert('Необходимо загрузить видеофайл');
                        e.preventDefault();
                        return;
                    }
                }
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Автоматическое скрытие сообщений через 5 секунд
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });

    // Плавная прокрутка для всех ссылок
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Анимация при загрузке статистических карточек
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Инициализация всех tooltips
    const tooltipElements = document.querySelectorAll('[title]');
    tooltipElements.forEach(el => {
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('title');
            
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - 40) + 'px';
            tooltip.style.left = (rect.left + rect.width / 2) + 'px';
            tooltip.style.transform = 'translateX(-50%)';
            
            document.body.appendChild(tooltip);
            
            this.addEventListener('mouseleave', function() {
                tooltip.remove();
            }, { once: true });
        });
    });
});

// Добавляем стили для tooltip
const tooltipStyle = document.createElement('style');
tooltipStyle.textContent = `
.tooltip {
    position: fixed;
    background-color: #333;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    z-index: 1000;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.2s;
}
.tooltip::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 5px 5px 0;
    border-style: solid;
    border-color: #333 transparent transparent;
}
`;
document.head.appendChild(tooltipStyle);