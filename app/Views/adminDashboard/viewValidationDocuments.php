<?php require "sideBar.php"; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <?php include __DIR__ . "/../messageError.php"; ?>
    <?php $_SESSION["previous_page"] = $_SERVER['REQUEST_URI']; ?>
    <h1 class="bold text-2xl mb-4">Protocolos Validados</h1>

    <h3 class="text-lg text-blue-800 mb-2">Quantidade de Protocolos</h3>
    <form method="GET" class="mb-4">
        <select name="course_id" id="course" class="border-s border-grey-100 p-2 bg-gray-100" required>
            <option value="">Selecione um curso</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>" <?= (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select id="year_type" name="year_type" class="border-s border-grey-100 p-2 bg-gray-100"
            onchange="toggleYearSelect()">
            <option value="">Selecione o tipo de ano</option>
            <option value="civil" <?= (isset($_GET['year_type']) && $_GET['year_type'] == 'civil') ? 'selected' : '' ?>>Ano
                Civil</option>
            <option value="school" <?= (isset($_GET['year_type']) && $_GET['year_type'] == 'school') ? 'selected' : '' ?>>
                Ano Letivo</option>
        </select>
        <select name="civil_year" id="civil_year"
            class="border-s border-grey-100 p-2 bg-gray-100 <?= (!isset($_GET['year_type']) || (isset($_GET['year_type']) && $_GET['year_type'] == 'school') ? 'hidden' : '') ?>"
            <?= (isset($_GET['year_type']) && $_GET['year_type'] == 'civil') ? 'required' : '' ?>>
            <option value="">Selecione o ano civil</option>
            <?php foreach ($civilYears as $year): ?>
                <option value="<?= $year ?>" <?= (isset($_GET['civil_year']) && $_GET['civil_year'] == $year) ? 'selected' : '' ?>>
                    <?= $year ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="school_year" id="school_year" class="border-s border-grey-100 p-2 bg-gray-100 <?=
            (!isset($_GET['year_type']) ||
                (isset($_GET['year_type']) && $_GET['year_type'] == 'civil'))
            ? 'hidden' : '' ?>" <?= (isset($_GET['year_type']) && $_GET['year_type'] == 'school')
                ? 'required' : '' ?>>
            <option value="">Selecione o ano letivo</option>
            <?php foreach ($schoolYears as $year): ?>
                <option value="<?= $year ?>" <?= (isset($_GET['school_year']) && $_GET['school_year'] == $year) ? 'selected' : '' ?>>
                    <?= $year ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Ver quantidade
        </button>
    </form>

    <?php if (isset($protocolCount)): ?>
        <p class="text-gray-600 mb-4">Quantidade de protocolos: <span id="protocolCount"><?= $protocolCount ?></span></p>
    <?php endif; ?>

    <h3 class="text-lg text-blue-800 mb-2">Gestão de Protocolos</h3>
    <form method="GET" class="flex items-center gap-2 mb-4">
        <select name="select_school_year" id="select_school_year" class="border-s border-grey-100 p-2 bg-gray-100">
            <?php foreach ($schoolYears as $year): ?>
                <option value="<?= $year ?>" <?= (isset($_GET['select_school_year']) && $_GET['select_school_year'] == $year) ? 'selected' : '' ?>>
                    <?= $year ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Filtrar
        </button>
    </form>

    <ul class="mt-2">
        <?php if (count($documents) > 0): ?>
            <?php foreach ($documents as $document): ?>
                <li class="flex p-2 border-b">
                    <div class="flex-grow">
                        <div onclick="document.getElementById('documentForm<?php echo $document['final_document_id']; ?>').submit()"
                            class="flex-grow w-full items-start">
                            <?php
                            echo htmlspecialchars($document["name"] ?? '') . " - " .
                                date("d/m/Y H:i", strtotime($document["created_at"] ?? 'now'));
                            ?>
                        </div>
                        <span class="text-cyan-600">Validado</span>
                    </div>
                    <form method="GET" action="addition-document">
                        <div class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <input type="submit" value="Aditamento" id="aditamento">
                        </div>
                    </form>
                    <form method="POST" action="cancel-document">
                        <div class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <input type="hidden" name="final_document_id"
                                value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                            <input type="hidden" name="status" value="Inativo">
                            <input type="submit" value="Anular" id="anular"
                                onclick="return confirm('Tem certeza que deseja anular este documento?')">
                        </div>
                    </form>
                    <form method="POST" action="print-pdf" target="_blank">
                        <input type="hidden" name="final_document_id"
                            value="<?php echo htmlspecialchars($document["final_document_id"]); ?>">
                        <button type="submit">
                            <img class="h-10 cursor-pointer" src="/images/print_icon.webp">
                        </button>
                    </form>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="p-2 text-gray-500">Nenhum utilizador encontrado.</li>
        <?php endif; ?>
    </ul>
    <!-- Paginação -->
    <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Mostrando <span class="font-medium"><?= $startRecord ?></span> a
            <span class="font-medium"><?= $endRecord ?></span> de
            <span class="font-medium"><?= $totalRecords ?></span> registos
        </div>
        <div class="flex gap-2">
            <?php
            $queryParams = $_GET;
            $queryParams['page'] = $currentPage - 1;
            $prevPageUrl = '?' . http_build_query($queryParams);

            $queryParams['page'] = $currentPage + 1;
            $nextPageUrl = '?' . http_build_query($queryParams);
            ?>
            <?php if ($currentPage > 1): ?>
                <a href="<?= $prevPageUrl ?>" class="px-4 py-2 border rounded text-sm">Anterior</a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $nextPageUrl ?>" class="px-4 py-2 border rounded text-sm">Próximo</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/js/yearType.js"></script>