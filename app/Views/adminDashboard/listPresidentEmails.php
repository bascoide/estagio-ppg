<?php require "sideBar.php"; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <form action="" method="POST">
        <?php $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
        <h1 class="bold text-2xl">Emails presidenciais</h1>

        <input type="email" placeholder="Email presidencial" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            name="new_president_email" required>
        <br>

        <button type="submit"
                class="mt-4 p-2 bg-blue-500 w-full cursor-pointer hover:bg-blue-600 text-white">Adicionar</button>
    </form>

    <ul class="mt-4">
        <?php if (!empty($presidentEmails)): ?>
            <?php foreach ($presidentEmails as $email):
                if (!is_array($email) || !isset($email['id'], $email['email'])) {
                    continue;
                }
                ?>
                <li class="flex items-center p-2 border-b hover:bg-gray-50">
                    <div class="flex-grow">
                        <form id="documentForm<?php echo htmlspecialchars($email['id']); ?>" method="POST">
                            <input type="hidden" name="president_email" value="<?= htmlspecialchars($email["email"]) ?>">
                            <div class="flex-grow w-full items-start cursor-pointer">
                                <?= htmlspecialchars($email["email"]) ?>
                            </div>
                        </form>
                    </div>
                    <form method="POST" action="/delete-president-email" class="ml-4">
                        <input type="hidden" name="email_id" value="<?= htmlspecialchars($email['id']) ?>">
                        <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer p-1 rounded-full hover:bg-red-100">
                            ×
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="p-2 text-gray-500">Nenhum aditamento encontrado.</li>
        <?php endif; ?>
    </ul>
</div>
