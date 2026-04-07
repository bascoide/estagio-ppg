document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nif-input').forEach(input => {
        const fieldId = input.id.split('_')[1];
        const errorElement = document.getElementById(`nif-error-${fieldId}`);

        input.addEventListener('blur', function (e) {
            const nif = e.target.value;
            if (nif && !validateNIF(nif)) {
                errorElement.classList.remove('hidden');
                input.classList.add('border-red-500');
            } else {
                errorElement.classList.add('hidden');
                input.classList.remove('border-red-500');
            }
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                const nif = input.value;
                if (nif && !validateNIF(nif)) {
                    e.preventDefault();
                    errorElement.classList.remove('hidden');
                    input.classList.add('border-red-500');
                    input.focus();
                }
            });
        }
    });
});

function validateNIF(nif) {
    if (!/^\d{9}$/.test(nif)) return false;

    const checkDigit =
        parseInt(nif[0]) * 9 +
        parseInt(nif[1]) * 8 +
        parseInt(nif[2]) * 7 +
        parseInt(nif[3]) * 6 +
        parseInt(nif[4]) * 5 +
        parseInt(nif[5]) * 4 +
        parseInt(nif[6]) * 3 +
        parseInt(nif[7]) * 2;

    const remainder = checkDigit % 11;
    const controlDigit = remainder < 2 ? 0 : 11 - remainder;
    return controlDigit === parseInt(nif[8]);
}