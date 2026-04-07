function toggleRejectionReason() {
    const rejectionContainer = document.getElementById('rejectionReasonContainer');
    const isRejected = document.getElementById('recusar').checked;

    rejectionContainer.classList.toggle('hidden', !isRejected);

    const checkboxes = document.querySelectorAll('input[name^="rejected_fields"]');
    
    if (isRejected) {
        checkboxes.forEach(function(checkbox) {
            checkbox.classList.remove('hidden');
            checkbox.classList.add('mr-2');
        });
    } else {
        checkboxes.forEach(function(checkbox) {
            checkbox.classList.add('hidden');
            checkbox.classList.remove('mr-2');
            checkbox.checked = false;
        });
    }

    const reasonInput = document.getElementById('rejectionReason');
    if (!isRejected) {
        reasonInput.value = '';
    }
}

document.addEventListener('DOMContentLoaded', toggleRejectionReason);