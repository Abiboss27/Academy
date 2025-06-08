document.addEventListener('DOMContentLoaded', () => {
    const courseSelect = document.getElementById('course');
    const amountInput = document.getElementById('amount');

    // Обновление суммы при выборе курса
    courseSelect.addEventListener('change', async () => {
        const courseId = courseSelect.value;
        const response = await fetch(`get_course_price.php?course_id=${courseId}`);
        const data = await response.json();
        amountInput.value = data.price;
    });

    // Обработка формы
    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const response = await fetch('process_payment.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        document.getElementById('paymentStatus').innerHTML = 
            result.success 
            ? `<div class="success">✅ ${result.message}</div>`
            : `<div class="error">❌ ${result.message}</div>`;
    });
});
// Добавить в payment_script.js
fetch(`get_course_price.php?course_id=${courseId}`)
  .then(response => {
    if (!response.ok) throw new Error('Ошибка сети');
    return response.json();
  })
  .catch(error => {
    paymentStatus.innerHTML = `<div class="error">⚠️ ${error.message}</div>`;
  });
