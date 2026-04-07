function editCourseName(courseId, currentName) {
    const newName = prompt('Editar nome do curso:', currentName);

    if (newName !== null && newName !== currentName) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/edit-course';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = courseId;
        form.appendChild(idInput);

        const nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'new_name';
        nameInput.value = newName;
        form.appendChild(nameInput);

        document.body.appendChild(form);
        form.submit();
    }
}

function confirmDelete(courseId, event) {
    event.preventDefault();

    if (confirm('Tem certeza que deseja excluir este curso permanentemente?')) {
        document.getElementById("delete-course-form-" + courseId).submit();
    }
}
