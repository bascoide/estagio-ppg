<?php
require __DIR__ . '/../vendor/autoload.php';
session_start();
?>

<!DOCTYPE html>
<html lang="pt-pt" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Ricardo da Costa Ferreira">
    <meta name="author" content="Simão Pedro Carvalho Ferreira">
    <link href="./output.css" rel="stylesheet">
    <title>PPG</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
</head>

<body class="">
    <div class="flex p-5 bg-gray-200">
        <img src="../images/logo.webp" alt="logo" class="h-12 transition delay-150 duration-300 ease-in-out">
        <div class="ml-auto">
            <?php
            if (isset($_SESSION['user_id'])) {
                echo "<a href='/logout' class='flex items-center gap-1 group'>
                <img src='/images/logout.webp' alt='Logout'
                    class='w-8 h-8 transition duration-300 group-hover:brightness-150' />
                <span class='text-gray-700 group-hover:text-blue-500 transition text-lg'>Logout</span> </a>";
            }
            ?>
        </div>
    </div>

    <div class="w-full bg-gray-100 flex justify-center">
        <?php require '../routes/web.php'; ?>
    </div>

    <div class="p-5 bg-gray-200 flex justify-center items-center">
        <div class="text-center">
            <p>PPG © 2025 - ISCAP</p>
            <p>Todos os direitos reservados.</p>
        </div>
    </div>
</body>

</html>