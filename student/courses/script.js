document.addEventListener('DOMContentLoaded', () => {
    const contentArea = document.querySelector('.content-area');

    document.querySelectorAll('.content-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();

            const type = link.getAttribute('data-type');
            const id = link.getAttribute('data-id');

            fetch(`content.php?type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        contentArea.innerHTML = `<p style="color:red;">Ошибка: ${data.error}</p>`;
                        return;
                    }

                    if (data.type === 'lesson') {
                        let html = `<h2>${escapeHtml(data.title)}</h2>`;
                        html += `<p>${escapeHtml(data.summary).replace(/\n/g, '<br>')}</p>`;
                        if (data.video_url) {
                            html += `<video width="100%" controls><source src="${escapeHtml(data.video_url)}" type="video/mp4">Ваш браузер не поддерживает видео.</video>`;
                        }
                        if (data.attachment) {
                            html += `<p>Вложения: <a href="${escapeHtml(data.attachment)}" target="_blank">Скачать</a></p>`;
                        }
                        contentArea.innerHTML = html;
                    } else if (data.type === 'test') {
                        let html = `<h2>Тест: ${escapeHtml(data.title)}</h2>`;
                        html += `<p>Длительность: ${escapeHtml(data.duration)}</p>`;
                        // Здесь можно добавить интерфейс прохождения теста
                        contentArea.innerHTML = html;
                    }
                })
                .catch(() => {
                    contentArea.innerHTML = '<p style="color:red;">Ошибка загрузки данных.</p>';
                });
        });
    });
});

// Функция экранирования HTML
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
