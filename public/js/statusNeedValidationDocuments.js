function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email.trim());
}

function rejectDocument(finalDocumentId, event) {
    event.preventDefault();
    let response = confirm('Tem a certeza que deseja rejeitar este documento?');

    if (response === true) {
        let rejectionReason = prompt("Por favor, insira o motivo da rejeição:");
        if (rejectionReason === null || rejectionReason.trim() === '') {
            alert('É necessário fornecer um motivo para a rejeição.');
            return false; // Prevent form submission
        }

        document.getElementById('rejectionReason' + finalDocumentId).value = rejectionReason;
        document.getElementById('documentRejectForm' + finalDocumentId).submit();
    }
    return false;
}

function validateDocument(finalDocumentId, event) {
    event.preventDefault();
    const presidentialEmail = document.getElementById('presidential_email');

    if (!isValidEmail(presidentialEmail.value)) {
        alert("Insira um email válido para o presidente!");
        return false;
    }

    let response = confirm('Tem a certeza que deseja validar este documento?');
    if (response === true) {
        document.getElementById('documentValidationForm' + finalDocumentId).submit();
    }
    return false;
}

document.addEventListener('DOMContentLoaded', function () {
    const presidentialEmail = document.getElementById('presidential_email');
    if (presidentialEmail) {
        presidentialEmail.addEventListener('input', function () {
            const selectedValue = this.value;
            const hiddenInputs = document.querySelectorAll('.hidden_presidencial_email');
            hiddenInputs.forEach(function (input) {
                input.value = selectedValue;
            });
        });
    }
});
