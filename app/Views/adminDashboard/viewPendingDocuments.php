<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <h1 class="bold text-2xl">Protocolos Pendentes</h1>

    <ul class="mt-4">
        <?php if (count($documents) > 0): ?>
            <?php foreach ($documents as $document):
                if ($document['document_type'] === 'Plano') {
                    continue;
                } ?>
                <li class="flex p-2 border-b" id="document-<?php echo $document['final_document_id']; ?>">
                    <form method="GET" action="view-final-document" class="flex-grow"
                        id="documentForm<?php echo $document['final_document_id']; ?>">
                        <input type="hidden" name="final_document_id"
                            value="<?php echo htmlspecialchars($document["final_document_id"] ?? ''); ?>">
                        <input type="hidden" name="document_id"
                            value="<?php echo htmlspecialchars($document["document_id"] ?? ''); ?>">
                        <div onclick="document.getElementById('documentForm<?php echo $document['final_document_id']; ?>').submit()"
                            class="flex-grow w-full items-start cursor-pointer">
                            <?php
                            echo htmlspecialchars($document["name"] ?? '') . " - " .
                                date("d/m/Y H:i", strtotime($document["created_at"] ?? 'now'));
                            ?>
                        </div>
                        <span class="text-yellow-600">Pendente</span>
                    </form>

                    <div class="flex items-center">
                        <?php if ($document['plan_is_verified'] == true): ?>
                            <form method="POST" action="view-plan" target="_blank" class="plan-form">
                                <input type="hidden" name="final_document_id"
                                    value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                                <input type="hidden" name="plan_path"
                                    value="<?php echo htmlspecialchars($document["plan_path"]); ?>">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 rounded-lg p-1 mr-2">
                                    <img class="h-10 cursor-pointer" src="/images/plan_icon.webp">
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="view-plan" target="_blank" class="plan-form"
                                id="planForm<?php echo $document['final_document_id']; ?>"
                                onsubmit="event.preventDefault(); verifyPlan(<?php echo $document['final_document_id']; ?>, <?php echo $document['plan_id']; ?>, '<?php echo htmlspecialchars($document["plan_path"]); ?>', this);">
                                <input type="hidden" name="final_document_id"
                                    value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                                <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($document["plan_id"]); ?>">
                                <input type="hidden" name="plan_path"
                                    value="<?php echo htmlspecialchars($document["plan_path"]); ?>">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 rounded-lg p-1 mr-2">
                                    <img class="h-10 cursor-pointer" src="/images/plan_icon.webp">
                                </button>
                            </form>
                        <?php endif; ?>

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
            <li class="p-2 text-gray-500">Nenhum utilizador encontrado.</li>
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

<script src='/js/pendingDocuments.js'></script>