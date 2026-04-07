<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <h1 class="bold text-2xl">Documentos</h1>
    <br>

    <!-- Formulário de Pesquisa -->
    <form method="GET">
        <input type="text" name="search" placeholder="Pesquisar documentos..."
            class="border-gray-300 border p-2 rounded w-1/2"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

        <select name="show" class="border p-2 rounded">
            <?php
            // Verifica se o parâmetro 'show' está definido na URL e define o valor padrão como 'Active'
            $show = isset($_GET['show']) ? $_GET['show'] : 'Active';
            ?>
            <option value="Active" <?php echo ($show == 'Active') ? 'selected' : ''; ?>>
                Ativos</option>
            <option value="Inactive" <?php echo ($show == 'Inactive') ? 'selected' : ''; ?>>Inativos</option>
        </select>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filtrar</button>
    </form>

    <ul class="mt-4">
    <?php
    // Capturar o termo de pesquisa
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $documentsFound = false;

    // Filtrar e exibir documentos
    if (count($documents) > 0):
        foreach ($documents as $document):
            if ($show === 'Active' && $document["is_active"] === 0) {
                continue;
            } else if ($show === 'Inactive' && $document["is_active"] === 1) {
                continue;
            }

            if ($search === '' || stripos($document["name"], $search) !== false):
                $documentsFound = true; ?>
                <li class="p-2 border-b border-gray-300 list-none flex">
                    <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                    <div class="flex-grow">
                        <span class="text-blue-500">
                            <?php echo htmlspecialchars($document["name"]); ?>
                        </span>
                        <span class="ml-2 text-gray-500">
                            <?php echo $document["type"] === "Plano" ? "(Plano)" : "(Protocolo)"; ?>
                        </span>
                    </div>
                    <form method="POST" action="print-document" target="_blank">
                        <input type="hidden" name="document_id" value="<?php echo htmlspecialchars($document["id"]); ?>">
                        <button type="submit">
                            <img class="h-10 cursor-pointer" src="/images/print_icon.webp" title="PDF do documento limpo">
                        </button>
                    </form>
                    <form method="POST" action="download-docx" target="_blank">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($document["id"]); ?>">
                        <button type="submit">
                            <img class="h-10 cursor-pointer" src="/images/download_icon.webp" title="Download do docx (apenas para a edição dos campos)">
                        </button>
                    </form>
                    <?php if ($document["is_active"] === 1): ?>
                        <form method="POST" action="deactivate-document"
                            id="deactivate-document-form-<?php echo htmlspecialchars($document['id']) ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($document["id"]); ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($document["name"]); ?>">
                            <button onclick="deleteDocumentPrompt(event, <?php echo htmlspecialchars($document['id']) ?>);">
                                <img class="h-10 cursor-pointer" src="/images/eliminar_documento.webp" title="Desativar documento (soft delete)" >
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="activate-document"
                            id="activate-document-form-<?php echo htmlspecialchars($document['id']) ?>">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($document["id"]); ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($document["name"]); ?>">
                            <button onclick="restoreDocumentPrompt(event, <?php echo htmlspecialchars($document['id']) ?>);">
                                <img class="h-10 cursor-pointer" src="/images/restaurar.webp" title="Restaurar documento">
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endif;
        endforeach;
    endif;

    // Mostrar mensagem se nenhum documento foi encontrado após filtragem
    if (!$documentsFound): ?>
        <li class="p-2 text-gray-500 list-none">
            <?php if ($show === 'Active'): ?>
                Nenhum documento ativo encontrado.
            <?php else: ?>
                Nenhum documento inativo encontrado.
            <?php endif; ?>
        </li>
    <?php endif; ?>
    </ul>
</div>

<script src="/js/showDocuments.js"></script>
