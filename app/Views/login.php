<div class="max-w-3xl w-full sm:p-10 p-6 bg-white mt-10 shadow-lg sm:mx-10 mx-4 mb-10">
    <h3 class="bold text-2xl">Login</h3>

    <?php include "messageError.php"; ?>

    <form method="POST" action="">
        <input type="email" name="email" class="mt-4 border-s border-grey-100 p-2 bg-gray-200 w-full"
            placeholder="Email" required>
        <br>
        <input type="password" name="password" class="mt-4 border-s border-grey-100 p-2 bg-gray-200 w-full"
            placeholder="Password" id="passwordInput" required>
        <br>
        <div class="mt-4">
            <input type="checkbox" name="Password" onclick="showPassword()" id="showPass">
            <label for="showPass">Mostrar Palavra-passe</label>
        </div>
        <br>
        <a href="/register" class="mt-8 text-blue-500 hover:text-blue-700 hover:underline">Não tem uma conta?
            Registe-se! </a>
        <input type="submit" class="mt-4 p-2 bg-blue-900 w-full cursor-pointer hover:bg-blue-600 text-white"
            value="Iniciar sessão" name="login">
    </form>
    <br>
</div>

<script src="/js/accountUser.js"></script>