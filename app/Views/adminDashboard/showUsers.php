<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <h1 class="bold text-2xl">Utilizadores</h1>
    <br>

    <!-- Formulário de Pesquisa -->
    <form method="GET">
        <input type="text" name="search" placeholder="Pesquisar utilizadores..."
            class="border-gray-300 border p-2 rounded w-1/2"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Pesquisar
        </button>
    </form>

    <ul class="mt-4">
    <?php
    // Capturar o termo de pesquisa
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Filtrar e exibir utilizadores
    if (count($users) > 0):
        foreach ($users as $user):
            if ($user["admin"])
                continue;
            if ($search === '' || stripos($user["name"], $search) !== false): ?>
                <li class="p-2 border-b border-gray-300 list-none hover:bg-gray-50 rounded transition">
                    <form method="GET" action="user-documents">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="text-left w-full cursor-pointer">
                            <span class="text-blue-500 cursor-pointer">
                                <?php echo htmlspecialchars($user["name"]); ?>
                            </span>
                            <span class="ml-2 text-gray-500">
                                (Utilizador)
                            </span>
                        </button>
                    </form>
                </li>
            <?php endif;
        endforeach;
    else: ?>
        <li class="p-2 text-gray-500">Nenhum utilizador encontrado.</li>
    <?php endif; ?>
    </ul>
</div>