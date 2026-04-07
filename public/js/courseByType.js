document.addEventListener('DOMContentLoaded', function() {
    const typeCourse = document.getElementById('typecourse');
    const courseSelect = document.getElementById('course');

    courseSelect.disabled = true;

    typeCourse.addEventListener('change', function() {
        const selectedType = this.value; 

        courseSelect.innerHTML = '<option value="">Selecione um curso</option>';

        if (!selectedType) {
            courseSelect.disabled = true;
            return;
        }

        // Filtra os cursos pelo tipo selecionado
        const filteredCourses = allCourses.filter(c => c.type_course_id == selectedType);

        filteredCourses.forEach(course => {
            const option = document.createElement('option');
            option.value = course.id;
            option.textContent = course.name;
            if (!course.is_active) {
                option.style.color = 'gray'; 
                option.style.backgroundColor = '#dddddd'; //cinzento 
            }
            courseSelect.appendChild(option);
        });

        courseSelect.disabled = filteredCourses.length === 0;
    });
});