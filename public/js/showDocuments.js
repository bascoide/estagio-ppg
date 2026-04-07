function deleteDocumentPrompt(event, documentId) {
    event.preventDefault();

    response = confirm("Tem a certeza que quer apagar o documento?")
    if (response) {
        document.getElementById("deactivate-document-form-" + documentId).submit();
    }

}

function restoreDocumentPrompt(event, documentId) {
    event.preventDefault();

    response = confirm("Tem a certeza que quer restaurar o documento?")
    if (response) {
        document.getElementById("activate-document-form-" + documentId).submit();
    }

}
