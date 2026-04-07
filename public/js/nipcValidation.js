document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.nipc-input').forEach(input => {
        const fieldId = input.id.split('_')[1];
        const errorElement = document.getElementById(`nipc-error-${fieldId}`);

        input.addEventListener('blur', function (e) {
            validateNIPCField(input, errorElement);
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateNIPCField(input, errorElement, true)) {
                    e.preventDefault();
                }
            });
        }
    });
});

function validateNIPCField(input, errorElement, showError = true) {
    const nipc = input.value;
    const isValid = nipc ? validateNIPC(nipc) : true;

    if (showError) {
        errorElement.classList.toggle('hidden', isValid);
        input.classList.toggle('border-red-500', !isValid);
    }

    return isValid;
}

function validateNIPC(nipc) {
    if (!/^[5-9]\d{8}$/.test(nipc)) return false;

    const digits = nipc.split('').map(Number);
    const checkDigit =
        digits[0] * 9 +
        digits[1] * 8 +
        digits[2] * 7 +
        digits[3] * 6 +
        digits[4] * 5 +
        digits[5] * 4 +
        digits[6] * 3 +
        digits[7] * 2;

    const remainder = checkDigit % 11;
    const controlDigit = remainder < 2 ? 0 : 11 - remainder;
    return controlDigit === digits[8];
}