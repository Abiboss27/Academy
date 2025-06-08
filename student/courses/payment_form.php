
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата курса | Образовательная платформа</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e0e7ff;
            --secondary: #3f37c9;
            --dark: #1e1e24;
            --light: #f8f9fa;
            --success: #4cc9f0;
            --border-radius: 12px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--dark);
            line-height: 1.6;
        }

        .payment-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            transform: translateY(0);
            animation: fadeInUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .payment-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--success));
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            color: var(--dark);
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            display: inline-block;
            width: 100%;
        }

        h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: var(--primary);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 15px;
        }

        select, input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background-color: white;
            appearance: none;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%231e1e24' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 10px;
        }

        .payment-methods label {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .payment-methods label:hover {
            border-color: var(--primary);
        }

        .payment-methods input[type="radio"] {
            width: auto;
            margin-right: 12px;
            opacity: 0;
            position: absolute;
        }

        .payment-methods label::before {
            content: '';
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            margin-right: 12px;
            transition: var(--transition);
        }

        .payment-methods input[type="radio"]:checked + label::before {
            border-color: var(--primary);
            background-color: var(--primary);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3C/svg%3E");
            background-position: center;
            background-repeat: no-repeat;
            background-size: 10px;
        }

        .pay-button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .pay-button:active {
            transform: translateY(0);
        }

        #paymentStatus {
            margin-top: 20px;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
            font-weight: 500;
            display: none;
        }

        .icon {
            margin-right: 8px;
            font-size: 18px;
            vertical-align: middle;
        }

        @media (max-width: 600px) {
            .payment-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2><span class="icon">💳</span> Оплата курса</h2>
        <form id="paymentForm" method="POST" action="process_payment.php">
            <div class="form-group">
                <label for="course"><span class="icon">🎓</span> Выберите курс:</label>
                <select id="course" name="course_id" required>
                    <?php include 'get_courses.php'; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label><span class="icon">🔧</span> Тип оплаты:</label>
                <div class="payment-methods">
                    <input type="radio" id="credit_card" name="payment_type" value="credit_card" required>
                    <label for="credit_card">Кредитная карта</label>
                    
                    <input type="radio" id="paypal" name="payment_type" value="paypal">
                    <label for="paypal">PayPal</label>
                    
                    <input type="radio" id="bank_transfer" name="payment_type" value="bank_transfer">
                    <label for="bank_transfer">Банковский перевод</label>
                </div>
            </div>

            <div class="form-group">
                <label for="amount"><span class="icon">💵</span> Сумма к оплате:</label>
                <input type="number" id="amount" name="amount" step="0.01" readonly>
            </div>

            <button type="submit" class="pay-button">
                <span class="icon">✅</span> Оплатить сейчас
            </button>
        </form>
        <div id="paymentStatus"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.querySelector('.pay-button');
            const courseSelect = document.getElementById('course');
            const amountInput = document.getElementById('amount');
            const paymentForm = document.getElementById('paymentForm');
            const paymentStatus = document.getElementById('paymentStatus');

            // Анимация кнопки
            payButton.addEventListener('mouseenter', function() {
                this.style.background = 'linear-gradient(135deg, #3a56e8, #2a2bb5)';
            });
            payButton.addEventListener('mouseleave', function() {
                this.style.background = 'linear-gradient(135deg, var(--primary), var(--secondary))';
            });

            //  получения цены сразу из атрибута data-price
            courseSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.dataset.price;

                if (price) {
                    amountInput.value = price;
                } else {
                    amountInput.value = '';
                }
            });

            // Инициализация суммы при загрузке страницы
            if (courseSelect.value) {
                const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                const price = selectedOption.dataset.price;
                if (price) {
                    amountInput.value = price;
                }
            }

            //  отправки формы через AJAX
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                paymentStatus.textContent = 'Обработка оплаты...';
                payButton.disabled = true;

                const formData = new FormData(paymentForm);

                fetch(paymentForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    payButton.disabled = false;
                    if (data.success) {
                        paymentStatus.textContent = 'Оплата прошла успешно!';
                        //  редирект, например:
                        // window.location.href = 'thankyou.php';
                    } else if (data.error) {
                        paymentStatus.textContent = 'Ошибка: ' + data.error;
                    } else {
                        paymentStatus.textContent = 'Неизвестный ответ от сервера';
                    }
                })
                .catch(error => {
                    payButton.disabled = false;
                    paymentStatus.textContent = 'Ошибка запроса: ' + error;
                });
            });
        });
    </script>
</body>
