<div class="max-w-3xl w-full sm:p-10 p-6 bg-white mt-10 shadow-lg sm:mx-10 mx-4 mb-10">
    <h3 class="bold text-2xl">Register</h3>

    <?php include "messageError.php"; ?>

    <div id="errorDiv"></div>

    <form method="POST" action="" onsubmit="comparePassword(event)">
        <input type="text" name="name" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Nome Completo" required>
        <br>
        <input type="email" name="email" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Email" required>
        <br>
        <input type="password" name="password" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Password" id="passwordInput" required>
        <br>
        <input type="password" name="conf-password" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
            placeholder="Confirm password" id="comfirmPasswordInput" required>
        <br>
        <div class="flex gap-4">
            <select name="CourseType" id="typecourse" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full"
                required>
                <option value="">Selecione um tipo de curso</option>
                <?php foreach ($coursesTypes as $courseType): ?>
                    <option value="<?php echo $courseType["id"] ?>">
                        <?php echo htmlspecialchars($courseType["name"]) ?>
                    </option>
                <?php endforeach ?>
            </select>
            <select name="Course" id="course" class="mt-4 border-s border-grey-100 p-2 bg-gray-100 w-full" required>
                <option value="">Selecione um curso</option>
            </select>
        </div>
        <br>
        <a href="/login" class="text-blue-500 hover:text-blue-700 hover:underline">Já tem uma conta? Inicie sessão!</a>

        <input type="submit" class="mt-4 p-2 bg-blue-900 w-full cursor-pointer hover:bg-blue-600 text-white"
            value="Registar" name="register">
    </form>
</div>

<script>
    // Passa todos os cursos para o JavaScript
    const allCourses = <?php echo json_encode($courses); ?>;
    // curso: {
    //    id: number,
    //    name: string,
    //    type: number
    //    is_active: boolean
    //}
</script>

<script src="/js/accountUser.js"></script>
<script src="/js/coursebyType.js"></script>
