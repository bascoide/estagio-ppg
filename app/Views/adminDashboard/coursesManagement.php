<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md grow-y-0 overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php
    $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <h1 class="bold text-2xl mb-4">Cursos</h1>
    <!-- Adicionar Cursos -->
    <h3 class="text-lg text-blue-800 mb-2">Adicionar curso</h3>
    <form method="POST" action="/add-course" class="flex gap-4">
        <input type="text" name="course_name" placeholder="Nome do curso"
            class="border-gray-300 border p-2 rounded w-1/2">
        <select name="course_type" class="border-gray-300 border p-2 rounded">
            <?php foreach ($courseTypes as $courseType): ?>
                <option value="<?php echo htmlspecialchars($courseType['id']) ?>">
                    <?php echo htmlspecialchars($courseType['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="is_course_active" class="border-gray-300 border p-2 rounded">
            <option value="1">Ativo</option>
            <option value="0">Inativo</option>
        </select>

        <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
            Adicionar
        </button>
    </form>

    <br>
    <hr class="border-gray-300">
    <br>
    <!-- Gerir cursos -->
    <h3 class="text-lg text-blue-800 mb-2">Gestão de cursos</h3>
    <form method="GET" class="flex items-center gap-4 mb-6">
        <input type="text" name="course_name" placeholder="Pesquisar curso"
            class="border-gray-300 border p-2 rounded w-1/2"
            value="<?php echo htmlspecialchars($_GET['course_name'] ?? ''); ?>">

        <select name="is_active" class="border-gray-300 border p-2 rounded">
            <option value="">Todos</option>
            <option value="1" <?php echo (isset($_GET['is_active']) && $_GET['is_active'] == '1') ? 'selected' : ''; ?>>
                Ativo</option>
            <option value="0" <?php echo (isset($_GET['is_active']) && $_GET['is_active'] == '0') ? 'selected' : ''; ?>>
                Inativo</option>
        </select>

        <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer">
            Pesquisar
        </button>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Nome do Curso</th>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Status</th>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <?php echo htmlspecialchars($course['name']); ?>
                            <span class="text-gray-400">(<?php echo htmlspecialchars($course['type_course_name']) ?>)</span>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <span
                                class="px-2 py-1 rounded-full text-xs <?php echo $course['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $course['is_active'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200 flex">
                            <button
                                onclick="editCourseName(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['name']); ?>')"
                                class="text-blue-500 hover:text-blue-700 cursor-pointer mr-2">
                                Editar
                            </button>
                            <form method="POST" action="/course/toggle-status">
                                <input type="hidden" value="<?php echo $course['id']; ?>" name="id">
                                <button type="submit" class="text-yellow-500 hover:text-yellow-700 cursor-pointer mr-2">
                                    <?php echo $course['is_active'] ? 'Desativar' : 'Ativar'; ?>
                                </button>
                            </form>
                            <form method="POST" action="/delete-course"
                                onsubmit="confirmDelete(<?php echo htmlspecialchars($course['id']) ?>, event)"
                                id="delete-course-form-<?php echo htmlspecialchars($course['id']) ?>">
                                <input type="hidden" value="<?php echo htmlspecialchars($course['id']) ?>" name="course_id">
                                <button type="submit" class="text-red-500 hover:text-red-700 cursor-pointer">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($courses)): ?>
                    <tr>
                        <td colspan="3" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">
                            Nenhum curso encontrado
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="/js/coursesManagement.js"></script>