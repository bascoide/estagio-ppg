<div class="max-w-3xl w-full p-10 bg-white mt-10 shadow-lg mx-10 mb-10">
    <h3 class="bold text-2xl">Upload</h3>

    <?php
    if (!isset($_GET['final_document_id'])) {
        if (!isset($_SESSION['message'])) {
            $_SESSION['error'] = "Nenhum documento foi encontrado";
        }
        include "messageError.php";
        exit;
    }

    include "messageError.php";
    ?>

    <form method="POST" action="/user-upload-final-document?final_document_id=<?php echo $_GET['final_document_id']; ?>"
        enctype="multipart/form-data">
        <input type="email" name="email" class="mt-4 border-s border-grey-100 p-2 bg-gray-200 w-full"
            placeholder="Email" required>
        <br>
        <input type="password" name="password" class="mt-4 border-s border-grey-100 p-2 bg-gray-200 w-full"
            placeholder="Password" id="passwordInput" required>
        <br>
        <br>
        <input type="checkbox" name="Password" onclick="showPassword()" id="showPass">
        <label for="showPass">Mostrar Palavra-passe</label>
        <br>
        <br>
        <label for="document">Upload documento final (.pdf):</label>
        <input type="file" class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
            id="document" name="document" accept=".pdf" required>
        <br>
        <input type="submit" class="mt-4 p-2 bg-blue-900 w-full cursor-pointer hover:bg-blue-600 text-white"
            value="Submeter" name="submit">
    </form>
    <br>
</div>

<script src="/js/accountUser.js"></script>