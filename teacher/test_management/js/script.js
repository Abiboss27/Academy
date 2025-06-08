// Load sections when course is selected
function loadSections(courseId) {
    if (!courseId) {
        document.getElementById('sections-container').innerHTML = '';
        document.getElementById('tests-container').innerHTML = '';
        return;
    }
    
    fetch(`ajax_get_sections.php?course_id=${courseId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('sections-container').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}

// Load tests when section is selected
function loadTests(sectionId, courseId) {
    if (!sectionId || !courseId) {
        document.getElementById('tests-container').innerHTML = '';
        return;
    }
    
    fetch(`ajax_get_tests.php?section_id=${sectionId}&course_id=${courseId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('tests-container').innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
}

// Generate option fields based on selected count
function generateOptionFields() {
    const count = parseInt(document.getElementById('option-count').value);
    const type = document.getElementById('question-type').value;
    const container = document.getElementById('option-fields');
    
    container.innerHTML = '';
    
    for (let i = 0; i < count; i++) {
        const div = document.createElement('div');
        const inputType = type === 'multiple' ? 'checkbox' : 'radio';
        
        div.innerHTML = `
            <input type="${inputType}" name="correct_answers[]" value="${i}">
            <input type="text" name="options[]" placeholder="Option ${i + 1}" required>
        `;
        container.appendChild(div);
    }
}

// Update options field based on question type
function updateOptionsField() {
    const type = document.getElementById('question-type').value;
    const optionsContainer = document.getElementById('options-container');
    
    if (type === 'number') {
        optionsContainer.style.display = 'none';
    } else {
        optionsContainer.style.display = 'block';
        generateOptionFields();
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // If we're on the manage questions page, initialize option fields
    if (document.getElementById('question-form')) {
        updateOptionsField();
    }
});