<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen  p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <h1 class="bold text-2xl">Upload Documento</h1>
    <br>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <form action="/upload-document" method="POST" enctype="multipart/form-data">
        <input type="text" placeholder="Nome do documento" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            id="documentName" name="documentName" required>
        <br><br>

        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-8">
                <div class="flex items-center">
                    <label for="documentFile">Upload documento Word (.docx):</label>
                    <input type="file"
                        class="ml-2 border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                        id="documentFile" name="documentFile" accept=".docx" required>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <input type="radio" id="plano" name="documentType" value="Plano" required
                            class="cursor-pointer">
                        <label for="plano" class="cursor-pointer">Plano</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="radio" id="protocolo" name="documentType" value="Protocolo" required
                            class="cursor-pointer">
                        <label for="protocolo" class="cursor-pointer">Protocolo</label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <label>Tipo de Curso:</label>
                <div class="flex items-center gap-2">
                    <input type="checkbox"
                        class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                        id="documentLicenciatura" name="courseTypes[]" value="1">
                    <label for="documentLicenciatura" class="cursor-pointer">Licenciatura</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox"
                        class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                        id="documentMestrado" name="courseTypes[]" value="2">
                    <label for="documentMestrado" class="cursor-pointer">Mestrado</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox"
                        class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                        id="documentPosGraduacao" name="courseTypes[]" value="3">
                    <label for="documentPosGraduacao" class="cursor-pointer">Pós-Graduação</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox"
                        class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
                        id="documentCTeSP" name="courseTypes[]" value="4">
                    <label for="documentCTeSP" class="cursor-pointer">CTeSP</label>
                </div>
            </div>
        </div>
        <br>
        <button type="submit"
            class="mt-4 p-2 bg-blue-500 w-full cursor-pointer hover:bg-blue-600 text-white">Upload</button>
    </form>
</div>