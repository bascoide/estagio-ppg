<?php require 'sideBar.php'; ?>

<div class="flex flex-col h-screen w-full">
    <div class="flex-grow p-4 bg-white rounded shadow-md overflow-hidden w-full">
        <?php include __DIR__ . "/../messageError.php"; ?>
        <form method="POST" action=<?= $status === "Aceite" ? "cancel-document" :"edit-document" ?> class="h-full flex flex-col w-full">

            <input type="hidden" name="filled_plan_id" value="<?php echo htmlspecialchars($planId) ?>">

            <input type="hidden" name="final_document_id"
                value="<?php echo htmlspecialchars($_GET['final_document_id']) ?>">

            <div class="overflow-y-auto flex-grow w-full">
                <table class="min-w-full text-sm text-left text-gray-700 w-full">
                    <thead class="bg-gray-200 text-gray-800 text-xs uppercase sticky top-0">
                        <tr>
                            <th class="px-4 py-3 w-1/3">Campo</th>
                            <th class="px-4 py-3 w-1/3">Valor Atual</th>
                            <?php
                            if ($status !== "Inativo" && $status !== "Aceite" && $status !== "Recusado") {
                                echo '<th class="px-4 py-3 w-1/3">Novo Valor</th>';
                            } ?>
                        </tr>
                    </thead>
                    <tbody class="w-full">
                        <?php
                        // Criar mapeamento de ID's de campo para nomes e tipos
                        $fieldsMap = [];
                        $fieldTypes = [];
                        foreach ($fieldNames as $field) {
                            $fieldsMap[$field['id']] = $field['name'];
                            $fieldTypes[$field['id']] = $field['data_type'];
                        }

                        // Mostrar cada campo com o seu valor
                        foreach ($fieldValues as $fieldValue) {
                            $fieldId = $fieldValue['field_id'];
                            $fieldName = $fieldsMap[$fieldId] ?? 'Campo Desconhecido';
                            $fieldType = $fieldTypes[$fieldId] ?? '';
                            $value = $fieldValue['value'] ?? '';

                            echo "<tr class='border-b w-full'>
                                <td class='px-4 py-3 font-medium w-1/3'>
                                    <input type='checkbox' 
                                        name='rejected_fields[" . htmlspecialchars($fieldName) . "]' 
                                        value='" . htmlspecialchars($value) . "' 
                                        class='mr-2 hidden'>"
                                . htmlspecialchars($fieldName) . "</td>
                                <td class='px-4 py-3 w-1/3'>" . htmlspecialchars($value) . "</td>";

                            if ($status !== "Inativo" && $status !== "Aceite" && $status !== "Recusado") {
                                echo "<td class='px-4 py-3 w-1/3'>
                                    <input type='hidden' name='field_names[" . htmlspecialchars($fieldId) . "]' 
                                        value='" . htmlspecialchars($fieldName) . "'>";

                                // Verifica se o campo é do tipo professor usando o array correto
                                if (stripos($fieldType, 'professor') !== false) {
                                    $datalistId = "professors-" . htmlspecialchars($fieldId);

                                    echo "<input type='text' 
                                            list='{$datalistId}'
                                            name='fields[" . htmlspecialchars($fieldId) . "]'
                                            value='" . htmlspecialchars($value) . "'
                                            class='w-full border rounded px-2 py-1'
                                            placeholder='Digite ou selecione um professor'>
                                          <datalist id='{$datalistId}'>";

                                    foreach ($teachers as $teacher) {
                                        echo "<option value='" . htmlspecialchars($teacher['name']) . "'>";
                                    }

                                    echo "</datalist>";
                                } else {
                                    // Campo normal (text input)
                                    echo "<input type='text'
                                        name='fields[" . htmlspecialchars($fieldId) . "]'
                                        value='" . htmlspecialchars($value) . "'
                                        class='w-full border rounded px-2 py-1'>";
                                }

                                echo "</td></tr>";
                            }
                        }
                        ?>
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId) ?>">
                        <input type="hidden" name="document_id" value="<?php echo htmlspecialchars($documentId) ?>">
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end sticky bottom-0 bg-white pt-2 pb-2 w-full">
                <div class="flex flex-grow">
                    <?php if ($status !== "Inativo" && $status !== "Aceite" && $status !== "Recusado"): ?>
                        <div class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <label for="aceitar">Aceitar</label>
                            <input type="radio" name="status" value="Aceite" id="aceitar" <?= ($status === 'Aceite') ? 'checked' : '' ?> onchange="toggleRejectionReason()">
                        </div>
                        <div class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <label for="recusar">Recusar</label>
                            <input type="radio" name="status" value="Recusado" id="recusar" <?= ($status === 'Recusado') ? 'checked' : '' ?> onchange="toggleRejectionReason()">
                        </div>
                        <div class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <label for="pendente">Pendente</label>
                            <input type="radio" name="status" value="Pendente" id="pendente" <?= ($status === 'Pendente') ? 'checked' : '' ?> onchange="toggleRejectionReason()">
                        </div>
                        <div id="rejectionReasonContainer" class="hidden ml-4 mr-4 flex-grow">
                            <input type="text" name="rejection_reason" id="rejectionReason" placeholder="Motivo da rejeição"
                                class="w-full border rounded px-2 py-1">
                        </div>
                    <?php endif ?>
                    <?php if ($status === 'Aceite'): ?>
                        <div class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2">
                            <input type="hidden" name="status" value="Inativo">
                            <input type="submit" value="Anular" id="anular" onclick="return confirm('Tem certeza que deseja anular este documento?')">
                        </div>
                    <?php endif ?>
                </div>
                <button type="button"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-2 cursor-pointer"
                    onclick="history.go(-1)">
                    Voltar
                </button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded cursor-pointer <?php if ($status === 'Inativo' || $status === 'Aceite' || $status === 'Recusado')
                    echo "hidden"; ?>">
                    Guardar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script src="/js/rejectDocuments.js"></script>