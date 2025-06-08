document.addEventListener('DOMContentLoaded', function() {
    // Таймер для теста
    const timerElement = document.querySelector('.timer');
    if (timerElement) {
        const duration = timerElement.dataset.duration;
        const [hours, minutes, seconds] = duration.split(':').map(Number);
        let totalSeconds = hours * 3600 + minutes * 60 + seconds;
        
        const timeDisplay = document.getElementById('time-display');
        
        const timer = setInterval(function() {
            totalSeconds--;
            
            if (totalSeconds <= 0) {
                clearInterval(timer);
                document.getElementById('test-form').submit();
                return;
            }
            
            const hoursLeft = Math.floor(totalSeconds / 3600);
            const minutesLeft = Math.floor((totalSeconds % 3600) / 60);
            const secondsLeft = totalSeconds % 60;
            
            timeDisplay.textContent = 
                `${hoursLeft.toString().padStart(2, '0')}:${minutesLeft.toString().padStart(2, '0')}:${secondsLeft.toString().padStart(2, '0')}`;
        }, 1000);
    }
    
    // Проверка заполнения всех обязательных вопросов перед отправкой теста
    const testForm = document.getElementById('test-form');
    if (testForm) {
        testForm.addEventListener('submit', function(e) {
            const questions = document.querySelectorAll('.question');
            let allAnswered = true;
            
            questions.forEach(question => {
                const questionType = question.dataset.type;
                const questionId = question.querySelector('input, [type="number"]')?.name.match(/\[(\d+)\]/)[1];
                
                if (questionType === 'single') {
                    const isAnswered = question.querySelector(`input[name="answers[${questionId}]"]:checked`);
                    if (!isAnswered) {
                        allAnswered = false;
                        question.style.borderLeft = '3px solid #f39c12';
                    }
                } else if (questionType === 'multiple') {
                    const isAnswered = question.querySelector(`input[name="answers[${questionId}][]"]:checked`);
                    if (!isAnswered) {
                        allAnswered = false;
                        question.style.borderLeft = '3px solid #f39c12';
                    }
                } else if (questionType === 'number') {
                    const answer = question.querySelector(`input[name="answers[${questionId}]"]`).value;
                    if (answer === '') {
                        allAnswered = false;
                        question.style.borderLeft = '3px solid #f39c12';
                    }
                }
            });
            
            if (!allAnswered) {
                e.preventDefault();
                alert('Пожалуйста, ответьте на все вопросы перед отправкой теста.');
            }
        });
    }
    
    // Обновление прогресса курса
    function updateCourseProgress() {
        const courseId = new URLSearchParams(window.location.search).get('id');
        if (!courseId) return;
        
        fetch(`course_progress.php?course_id=${courseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.progress !== undefined) {
                    const progressFill = document.querySelector('.progress-fill');
                    const progressText = document.querySelector('.progress-text');
                    
                    if (progressFill) {
                        progressFill.style.width = `${data.progress}%`;
                    }
                    
                    if (progressText) {
                        progressText.textContent = `${data.progress}% завершено`;
                    }
                }
            })
            .catch(error => console.error('Ошибка при обновлении прогресса:', error));
    }
    
    // Обновляем прогресс каждые 30 секунд
    setInterval(updateCourseProgress, 30000);
    updateCourseProgress();
    
    // Анимация круга с результатом теста
    const scoreCircle = document.querySelector('.score-circle');
    if (scoreCircle) {
        const score = parseInt(scoreCircle.dataset.score);
        const circleFill = scoreCircle.querySelector('.circle-fill');
        
        // Уже анимируется через CSS transition
        // Но можно добавить дополнительную анимацию
        circleFill.style.strokeDasharray = `0, 100`;
        setTimeout(() => {
            circleFill.style.strokeDasharray = `${score}, 100`;
        }, 100);
    }
});