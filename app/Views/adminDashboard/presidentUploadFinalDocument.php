<div class="max-w-3xl w-full p-10 bg-white mt-10 shadow-lg mx-10 mb-10">
    <h3 class="bold text-2xl">Upload</h3>

    <?php
    if (!isset($_GET['uuid'])) {
        if (!isset($_SESSION['message'])) {
            $_SESSION['error'] = "Nenhum documento foi encontrado";
        }
        include __DIR__ . "/../messageError.php";
        exit;
    }

    include __DIR__ . "/../messageError.php";
    ?>

    <form method="POST" action="president-final-document" 
        enctype="multipart/form-data">
        <label for="document">Upload documento assinado (.pdf):</label>
        <input type="file" class="border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
            id="document" name="document" accept=".pdf" required>
        <br>
        <input type="hidden" name="verified_uuid" value="<?php echo htmlspecialchars($_GET['uuid']); ?>">
        <input type="submit" class="mt-4 p-2 bg-blue-900 w-full cursor-pointer hover:bg-blue-600 text-white"
            value="Submeter" name="submit">
    </form>
    <br>
</div>