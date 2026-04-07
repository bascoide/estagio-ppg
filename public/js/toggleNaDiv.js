const naStates = new Map();

function toggleContent(id) {
    const container = document.getElementById(`NA-${id}`);
    if (!container) return;

    // Inicializar se for a primeira vez a aceder a este campo
    if (!naStates.has(id)) {
        naStates.set(id, {
            original: null, // Será definido quando alternar para NA pela primeira vez
            isNA: false,
            inputs: [] // Será preenchido quando alternar para NA pela primeira vez
        });
    }

    const state = naStates.get(id);

    if (state.isNA) {
        // Restaurar conteúdo original
        container.innerHTML = state.original;

        // Restaurar os elementos de entrada originais com as suas propriedades
        state.inputs.forEach(inputData => {
            const { element, type, value, required } = inputData;
            element.type = type;
            element.value = value;
            element.required = required;
            const existingInput = container.querySelector(`[name="${element.name}"]`);
            if (existingInput) {
                existingInput.replaceWith(element);
            }
        });

        state.isNA = false;
    } else {
        // Guardar estado atual (incluindo modificações do utilizador)
        state.original = container.innerHTML;

        // Guardar estados dos campos atuais - apenas campos visíveis (não ocultos)
        state.inputs = Array.from(container.querySelectorAll('input:not([type="hidden"]), select, textarea'))
            .map(input => ({
                element: input,
                type: input.type,
                value: input.value,
                required: input.required,
                name: input.name
            }));

        // Converter apenas campos visíveis para campos NA ocultos
        state.inputs.forEach(({element}) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = element.name || `field_${id}`;
            hiddenInput.value = 'N/A';
            element.replaceWith(hiddenInput);
        });

        // Marcar elementos não-input como N/A
        container.querySelectorAll('label, span, p, div:not(:has(input))').forEach(el => {
            if (el.childNodes.length === 1 && el.childNodes[0].nodeType === Node.TEXT_NODE) {
                el.textContent = 'N/A';
            }
        });

        state.isNA = true;
    }
}
