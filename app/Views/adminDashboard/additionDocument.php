<?php require "sideBar.php"; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <form action="" method="POST" enctype="multipart/form-data">  <!-- Added enctype -->
        <?php $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
        <h1 class="bold text-2xl">Aditamentos</h1>

        <input type="text" placeholder="Nome do documento" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            id="documentName" name="name" required>  <!-- Changed name to match controller -->
        <br><br>

        <div class="flex items-center">
            <label for="documentFile">Upload documento:</label>
            <input type="file"
                class="ml-2 border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                id="documentFile" name="documentFile" accept=".pdf" required>  <!-- Changed from .docx to .pdf -->
        </div>

        <button type="submit"
                class="mt-4 p-2 bg-blue-500 w-full cursor-pointer hover:bg-blue-600 text-white">Upload</button>
    </form>
    <ul class="mt-4">
        <?php if (count($additions) > 0): ?>
            <?php foreach ($additions as $addition): ?>
                <li class="flex p-2 border-b">
                    <div class="flex-grow">
                        <form id="documentForm<?php echo $addition['final_document_id']; ?>" 
                              action="print-addition" method="POST" target="_blank">
                            <input type="hidden" name="document_id" value="<?php echo $addition['final_document_id']; ?>">
                            <input type="hidden" name="addition_path" value="<?php echo htmlspecialchars($addition['addition_path'] ?? ''); ?>">
                            <div onclick="document.getElementById('documentForm<?php echo $addition['final_document_id']; ?>').submit()"
                                class="flex-grow w-full items-start cursor-pointer">
                                <?php
                                echo htmlspecialchars($addition["name"] ?? '') . " - " .
                                    date("d/m/Y H:i", strtotime($addition["created_at"] ?? 'now'));
                                ?>
                            </div>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="p-2 text-gray-500">Nenhum aditamento encontrado.</li>
        <?php endif; ?>
    </ul>
</div>