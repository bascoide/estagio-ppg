<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">

    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <h1 class="bold text-2xl">Documentos do Utilizador</h1>
    <h2 class="text-gray-600 mt-2"><?php echo htmlspecialchars($userName); ?></h2>
    <br>

    <!-- Formulário de Filtro -->
    <form method="GET">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">

        <div class="flex space-x-4">
            <!-- Pesquisar pelo nome do documento -->
            <input type="text" name="search" placeholder="Pesquisar documentos..." class="border p-2 rounded w-1/3"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

            <!-- Filtrar por data -->
            <input type="date" name="date_filter" class="border p-2 rounded"
                value="<?php echo isset($_GET['date_filter']) ? htmlspecialchars($_GET['date_filter']) : ''; ?>">

            <!-- Ordenação -->
            <select name="order_by" class="border p-2 rounded">
                <option value="">Ordenar por...</option>
                <option value="date_newest" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'date_newest') ? 'selected' : ''; ?>>Mais recentes</option>
                <option value="date_oldest" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'date_oldest') ? 'selected' : ''; ?>>Mais antigos</option>
            </select>

            <!-- Status -->
            <select name="status" class="border p-2 rounded">
                <option value="">Status...</option>
                <option value="Pendente" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                <option value="Aceite" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Aceite') ? 'selected' : ''; ?>>Aceite</option>
                <option value="Recusado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Recusado') ? 'selected' : ''; ?>>Recusado</option>
                <option value="Por validar" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Por validar') ? 'selected' : ''; ?>>Por validar</option>
                <option value="Validado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Validado') ? 'selected' : ''; ?>>Validado</option>
                <option value="Invalidado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Invalidado') ? 'selected' : ''; ?>>Invalidado</option>
                <option value="Inativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Inativo') ? 'selected' : ''; ?>>Inativo</option>
            </select>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filtrar</button>
        </div>
    </form>

    <ul class="mt-4">
        <?php if (count($documents) > 0): ?>
            <?php foreach ($documents as $document):
                if ($document['status'] === 'Inativo') {
                    if (!isset($_GET['status'])) {
                        continue;
                    } else if ($_GET['status'] !== 'Inativo') {
                        continue;
                    }
                }

                if ($document['document_type'] === 'Plano') {
                    continue;
                } ?>

                <li class="flex p-2 border-b">
                    <?php
                    $validationStatuses = ['Validado', 'Por validar', 'Invalidado'];
                    $isValidationStatus = in_array($document['status'], $validationStatuses);
                    ?>

                    <form method="GET" action="view-final-document" class="flex-grow"
                        id="documentForm<?php echo $document['final_document_id']; ?>">
                        <input type="hidden" name="final_document_id"
                            value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                        <input type="hidden" name="document_id"
                            value="<?php echo htmlspecialchars($document["document_id"]); ?>">

                        <div class="flex-grow w-full items-start <?php echo !$isValidationStatus ? 'cursor-pointer' : ''; ?>"
                            <?php echo !$isValidationStatus ? 'onclick="document.getElementById(\'documentForm' . $document['final_document_id'] . '\').submit()"' : ''; ?>>
                            <?php
                            echo htmlspecialchars($document["document_name"]) . " - " .
                                date("d/m/Y H:i", strtotime($document["created_at"]));
                            ?>
                        </div>

                        <?php $needsValidation = false; ?>
                        <?php switch ($document['status'] ?? ''):
                            case 'Pendente': ?>
                                <span class="text-yellow-600">Pendente</span>
                                <?php break;
                            case 'Aceite': ?>
                                <span class="text-green-500">Aceite</span>
                                <?php break;
                            case 'Recusado': ?>
                                <span class="text-red-500">Recusado</span>
                                <?php break;
                            case 'Por validar': ?>
                                <span class="text-yellow-800 mr-4">Por validar</span>
                                <?php $needsValidation = true; ?>
                                <?php break;
                            case 'Validado': ?>
                                <span class="text-cyan-500">Validado</span>
                                <?php break;
                            case 'Inativo': ?>
                                <span class="text-gray-600">Inativo</span>
                                <?php break;
                            case 'Invalidado': ?>
                                <span class="text-purple-800">Invalidado</span>
                                <?php break;
                            default: ?>
                                <span class="text-gray-400">Desconhecido</span>
                        <?php endswitch; ?>
                    </form>

                    <div>
                        <?php if ($needsValidation === true): ?>
                            <div class="flex items-center gap-2 mr-2">
                                <form method="POST" action="validate-document"
                                    id="documentValidationForm<?php echo $document['final_document_id']; ?>">
                                    <input type="hidden" name="final_document_id"
                                        value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                                    <button class="bg-green-500 text-white rounded p-2 w-10 h-10 cursor-pointer hover:bg-green-600"
                                        onclick="validateDocument(<?php echo htmlspecialchars($document['final_document_id']); ?>, event)">
                                        ✔
                                    </button>
                                </form>

                                <form method="POST" action="invalidate-document"
                                    id="documentRejectForm<?php echo $document['final_document_id']; ?>">
                                    <input type="hidden" name="final_document_id"
                                        value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($u); ?>">
                                    <input type="hidden" name="rejection_reason"
                                        id="rejectionReason<?php echo $document['final_document_id']; ?>" value="">
                                    <button class="bg-red-500 text-white rounded p-2 w-10 cursor-pointer h-10 hover:bg-red-600"
                                        onclick="rejectDocument(<?php echo htmlspecialchars($document['final_document_id']); ?>, event)">
                                        ❌
                                    </button>
                                </form>
                            </div>
                        <?php endif ?>
                    </div>
                    <?php if ($document['status'] === 'Validado'): ?>
                        <form method="GET" action="addition-document">
                        <div class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <input type="submit" value="Aditamento" id="aditamento">
                        </div>
                        </form>
                        <form method="POST" action="cancel-document">
                            <div class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                                <input type="hidden" name="final_document_id"
                                    value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                                <input type="hidden" name="status" value="Inativo">
                                <input type="submit" value="Anular" id="anular"
                                    onclick="return confirm('Tem certeza que deseja anular este documento?')">
                            </div>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="print-pdf" target="_blank">
                        <input type="hidden" name="final_document_id"
                            value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                        <button type="submit">
                            <img class="h-10 cursor-pointer" src="/images/print_icon.webp">
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?></button>
            <li class="p-2 text-gray-500">Nenhum documento encontrado para este utilizador.</li>
        <?php endif; ?>
    </ul>
</div>

<script src="/js/statusNeedValidationDocuments.js"></script>