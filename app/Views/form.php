<div class="max-w-3xl w-full p-4 sm:p-10 bg-white mx-4 sm:mx-10 shadow-lg mb-10 grow-0">
    <?php include "messageError.php"; ?>

    <form action="/get-form" method="GET" class="md:flex items-center gap-x-4">

        <?php if (isset($_GET['filled_plan_id']) && $_GET['filled_plan_id']): ?>
        <input type="hidden" name="filled_plan_id" value="<?= htmlspecialchars($_GET['filled_plan_id']) ?>">
        <?php endif; ?>

        <label for="document" class="whitespace-nowrap">Documento:</label>
        <select name="document" id="document" class="mt-2 md:mt-0 w-full flex-grow p-2 border rounded-lg" required>
            <option value="">Selecione um documento</option>
            <?php foreach ($documents as $document): ?>
            <?php
            if ($document['is_active'] === 0) continue;

            if (isset($_GET['filled_plan_id']) === false) {
                if ($document['type'] !== 'Plano') continue;
            } else {
                if ($document['type'] !== 'Protocolo') continue;
            }
            ?>

            <option value="<?= $document['id'] ?>"><?= htmlspecialchars($document['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" class="w-full mt-2 md:mt-0 md:w-auto bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 cursor-pointer" value="Selecionar">
    </form>

    <?php if (!empty($fields)): ?>
        <!-- Botão de visualização de protocolos (vazios) -->
        <form method="POST" action="print-document" target="_blank">
            <input type="hidden" name="document_id" 
                value="<?php echo htmlspecialchars($_GET['document'] ?? ''); ?>">
            <button type="submit"
                class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 flex items-center gap-2">
                Visualizar Documento
                <img class="h-8" src="/images/document_view.webp" alt="Visualizar">
            </button>
        </form>

        <form action="/submit-form" method="POST" id="main-form" enctype="multipart/form-data">

        <?php if (isset($_GET['filled_plan_id']) && $_GET['filled_plan_id']): ?>
        <h3 class="text-3xl mt-4 text-blue-900">Preencha o Protocolo</h3>
        <label for="planFile">Faça upload do plano assinado: </label>
        <input
            type="file" name="planFile"
            class="ml-2 border p-1 cursor-pointer hover:bg-gray-300 rounded-lg border-gray-300 bg-gray-200"
            accept=".pdf" id="planFile" required>

        <?php else: ?>
        <h3 class="text-3xl mt-4 text-blue-900">Preencha o Plano</h3>
        <?php endif; ?>

        <input type="hidden" name="document_id" value="<?= $_GET['document'] ?? '' ?>">

        <?php
        $current_group = 0;
        ?>

        <?php foreach ($fields as $index => $field): ?>
        <?php
        switch ($field['data_type']) {
            case 'title':
                ?>
                <h3 class="text-2xl mt-4 text-blue-900"><?= htmlspecialchars($field['name']) ?></h3>
                <?php
                break;

            case 'group':
                $current_group++;
                break;

            case 'radio':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label>
                    <input type="radio"
                        name="group_<?= $current_group ?>"
                        value="<?= $index ?>"
                        <?= $field === reset($fields) ? 'checked' : '' ?>
                        class="mr-2"
                        onchange="updateRadioGroup(<?= $current_group ?>)"
                        required>
                    <?= htmlspecialchars($field['name']) ?>
                </label>
                <br>
                <input type="hidden" name="field_values[]" id="field_value_<?= $index ?>" value="<?= $field === reset($fields) ? 'true' : 'false' ?>">
                <?php
                break;

            case 'checkbox':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label>
                    <input type="checkbox"
                        id="checkbox_<?= $index ?>"
                        class="mr-2"
                        onchange="document.getElementById('field_value_<?= $index ?>').value = this.checked ? 'true' : 'false'">
                    <?= htmlspecialchars($field['name']) ?>
                </label>
                <br>
                <input type="hidden" name="field_values[]" id="field_value_<?= $index ?>" value="false">
                <?php
                break;

            case 'nif':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label for="field_<?= $field['id'] ?>"><?= htmlspecialchars($field['name']) ?>:</label>
                <input type="text"
                    name="field_values[]"
                    id="field_<?= $field['id'] ?>"
                    class="w-full p-2 border border-gray-300 bg-gray-50 rounded-lg nif-input"
                    pattern="\d{9}"
                    title="NIF deve conter 9 dígitos."
                    required>
                <div id="nif-error-<?= $field['id'] ?>" class="text-red-500 hidden mt-1">
                    NIF inválido. Por favor, insira um NIF válido.
                </div>
                <?php
                break;

            case 'nipc':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label for="field_<?= $field['id'] ?>"><?= htmlspecialchars($field['name']) ?>:</label>
                <input type="text"
                    name="field_values[]"
                    id="field_<?= $field['id'] ?>"
                    class="w-full p-2 border border-gray-300 bg-gray-50 rounded-lg nipc-input"
                    pattern="[5-9]\d{8}"
                    title="NIPC deve começar com 5-9 e conter 9 dígitos."
                    required>
                <div id="nipc-error-<?= $field['id'] ?>" class="text-red-500 hidden mt-1">
                    NIPC inválido. Deve começar com 5-9 e ter 9 dígitos válidos.
                </div>
                <?php
                break;

            case 'course':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <input type="hidden" name="field_values[]" value="<?= htmlspecialchars($userCourseName) ?>">
                <?php
                break;

            case 'professor':
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label><?= htmlspecialchars($field['name']) ?>:</label>
                <input list="field_<?= $field['id'] ?>" name="field_values[]" class="w-full p-2 border border-gray-300 bg-gray-50 rounded-lg">

                <datalist id="field_<?= $field['id'] ?>">
                    <?php foreach ($availableProfessors as $professor): ?>
                    <option value="<?php echo htmlspecialchars($professor['name']); ?>"></option>
                    <?php endforeach ?>
                </datalist>
                <?php
                break;

            case 'NA start':
                ?>
                <div class="na-toggle-container mt-2">
                    <input type="checkbox"
                        onclick="toggleContent(<?= $field['id'] ?>)"
                        id="na-toggle-<?= $field['id'] ?>">
                    <label for="na-toggle-<?= $field['id'] ?>">Não aplicável</label>

                    <div id="NA-<?= $field['id'] ?>">
                <?php
                break;

            case 'NA end':
                ?>
                    </div>
                </div>
                <hr class="my-6 border-gray-300">
                <?php
                break;

            default:
                ?>
                <input type="hidden" name="field_ids[]" value="<?= $field['id'] ?>">
                <input type="hidden" name="field_names[]" value="<?= htmlspecialchars($field['name']) ?>">
                <label for="field_<?= $field['id'] ?>"><?= htmlspecialchars($field['name']) ?>:</label>
                <input type="<?= htmlspecialchars($field['data_type']) ?>"
                    name="field_values[]"
                    id="field_<?= $field['id'] ?>"
                    class="w-full p-2 border border-gray-300 bg-gray-50 rounded-lg"
                    <?= !in_array($field['data_type'], ['checkbox', 'radio']) ? 'required' : '' ?>>
                <?php
                break;
        }
        ?>
        <?php endforeach; ?>

        <input class="mt-4 hover:bg-blue-600 bg-blue-900 p-2 w-full cursor-pointer text-white" type="submit" value="Submeter" />
    </form>
    <?php endif; ?>
</div>

<script src="/js/radioForm.js"></script>
<script src="/js/toggleNaDiv.js"></script>
<script src="/js/nifValidation.js"></script>
<script src="/js/nipcValidation.js"></script>
