<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen  p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <h3 class="bold text-2xl">Sign up Admin</h3>

    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <div id="errorDiv">

    </div>

    <form method="POST" action="" onsubmit="comparePassword(event)">
        <input type="email" name="email" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Email" required>
        <br>
        <input type="password" name="password" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Password" id="passwordInput" required>
        <br>
        <input type="password" name="conf-password" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Confirm password" id="comfirmPasswordInput" required>
        <br><br>
        <input type="submit" class="mt-4 p-2 bg-blue-500 w-full cursor-pointer hover:bg-blue-600 text-white"
            value="Registar" name="register">
    </form>
</div>

<script src="/js/accountUser.js"></script>