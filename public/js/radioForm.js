function updateRadioGroup(groupId) {
    // Pega todos os botões radio neste grupo
    const radios = document.querySelectorAll(`input[name="group_${groupId}"]`);

    // Atualiza todos os campos escondidos correspondentes
    radios.forEach(radio => {
        document.getElementById('field_value_' + radio.value).value =
            radio.checked ? 'true' : 'false';
    });
}

// Inicializa todos os grupos radio na página load
document.addEventListener('DOMContentLoaded', function () {
    // Encontra o maior grupo pelo nome do radio selecionado
    let maxGroup = 0;
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        const groupMatch = radio.name.match(/group_(\d+)/);
        if (groupMatch) {
            maxGroup = Math.max(maxGroup, parseInt(groupMatch[1]));
        }
    });

    // Inicializa cada grupo
    for (let i = 0; i <= maxGroup; i++) {
        updateRadioGroup(i);
    }
});