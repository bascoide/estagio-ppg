<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>

    <h1 class="bold text-2xl mb-4">Protocolos Por Validar</h1>
    <div class="flex gap-1">
        <select class="border p-2 rounded w-1/3 h-10" id="presidential_email">
            <option>Selecione um email presidencial</option>
            <?php foreach ($presidencialEmails as $email): ?>
                <option value="<?= $email["email"] ?>"><?= $email["email"] ?></option>
            <?php endforeach ?>
        </select>
        <form action="/president-list">
            <button type="submit"
                class="h-10 px-2 bg-blue-600 hover:bg-blue-700 cursor-pointer flex items-center justify-center">
                <img src="/images/hamburger-menu.png" class="h-6">
            </button>
        </form>
    </div>

    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <ul class="mt-4">
        <?php if (count($documents) > 0): ?>
            <?php foreach ($documents as $document): ?>
                <li class="flex p-2 border-b">
                    <div class="flex-grow">
                        <div onclick="document.getElementById('documentForm<?php echo $document['final_document_id']; ?>').submit()"
                            class="flex-grow w-full items-start">
                            <?php
                            echo htmlspecialchars($document["name"] ?? '') . " - " .
                                date("d/m/Y H:i", strtotime($document["created_at"] ?? 'now'));
                            ?>
                        </div>
                        <span class="text-yellow-800">Por validar</span>
                    </div>

                    <div class="flex items-center ml-4 gap-2">

                        <form method=POST action="validate-document"
                            id="documentValidationForm<?php echo $document['final_document_id']; ?>"></button>
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($document["user_id"]); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($document["email"]); ?>">
                            <input type="hidden" name="presidencial_email" class="hidden_presidencial_email">
                            <button class="bg-green-500 text-white rounded p-2 w-10 h-10 cursor-pointer hover:bg-green-600"
                                onclick="validateDocument(<?php echo htmlspecialchars($document['final_document_id']); ?>, event)">
                                ✔
                            </button>
                        </form>

                        <form method="POST" action="invalidate-document"
                            id="documentRejectForm<?php echo $document['final_document_id']; ?>">
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($document["email"]); ?>">
                            <input type="hidden" name="rejection_reason"
                                id="rejectionReason<?php echo $document['final_document_id']; ?>" value="">

                            <button class="bg-red-500 text-white rounded p-2 w-10 cursor-pointer h-10 hover:bg-red-600"
                                onclick="rejectDocument(<?php echo htmlspecialchars($document['final_document_id']); ?>, event)">
                                ❌
                            </button>
                        </form>
                        <form method="POST" action="print-pdf" target="_blank">
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <button type="submit">
                                <img class="h-10 cursor-pointer" src="/images/print_icon.webp">
                            </button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="p-2 text-gray-500 list-none">Nenhum utilizador encontrado.</li>
        <?php endif; ?>
    </ul>
    <!-- Paginação -->
    <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Mostrando <span class="font-medium"><?= $startRecord ?></span> a
            <span class="font-medium"><?= $endRecord ?></span> de
            <span class="font-medium"><?= $totalRecords ?></span> registos
        </div>
        <div class="flex gap-2">
            <?php
            $queryParams = $_GET;
            $queryParams['page'] = $currentPage - 1;
            $prevPageUrl = '?' . http_build_query($queryParams);

            $queryParams['page'] = $currentPage + 1;
            $nextPageUrl = '?' . http_build_query($queryParams);
            ?>
            <?php if ($currentPage > 1): ?>
                <a href="<?= $prevPageUrl ?>" class="px-4 py-2 border rounded text-sm">Anterior</a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $nextPageUrl ?>" class="px-4 py-2 border rounded text-sm">Próximo</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/js/statusNeedValidationDocuments.js"></script>