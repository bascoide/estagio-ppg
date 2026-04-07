function verifyPlan(finalDocumentId, planId, planPath, formElement) {
    // Mostrar status de carregamento
    const button = formElement.querySelector('button[type="submit"]');
    button.disabled = true;
    button.classList.replace('bg-red-600', 'bg-gray-400');
    button.classList.replace('hover:bg-red-700', 'hover:bg-gray-500');

    // 1. Primeira submissão do form para abrir o PDF em nova aba
    const newTab = window.open('', '_blank');
    fetch(formElement.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'final_document_id': finalDocumentId,
            'plan_id': planId,
            'plan_path': planPath
        })
    })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.blob();
        })
        .then(blob => {
            // Criar objeto URL e redirecionar a nova aba para ele
            const pdfUrl = URL.createObjectURL(blob);
            newTab.location.href = pdfUrl;

            // 2. Antes atualizar apenas o form (não o li enteiro)
            const newFormHTML = `
            <form method="POST" action="view-plan" target="_blank" class="plan-form">
                <input type="hidden" name="final_document_id" value="${finalDocumentId}">
                <input type="hidden" name="plan_path" value="${planPath}">
                <button type="submit" class="bg-green-600 hover:bg-green-700 rounded-lg p-1 mr-2">
                    <img class="h-10 cursor-pointer" src="/images/plan_icon.webp">
                </button>
            </form>
        `;

            // Recolocar apenas os elementos form
            formElement.outerHTML = newFormHTML;
        })
        .catch(error => {
            console.error('Error:', error);
            newTab.close();
            button.disabled = false;
            button.classList.replace('bg-gray-400', 'bg-red-600');
            button.classList.replace('hover:bg-gray-500', 'hover:bg-red-700');
            alert('Ocorreu um erro ao abrir o plano. Por favor, tente novamente.');
        });
}
