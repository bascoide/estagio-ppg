<?php require "sideBar.php"; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-4">Registo de Atividades</h1>

        <!-- Filtros -->
        <form method="GET" class="mb-4 flex gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Utilizador</label>
                <select name="logged_name" class="border p-2 rounded">
                    <option value="">Todos</option>
                    <?php foreach ($loggedNames as $logName): ?>
                        <option value="<?= $logName["name"] ?>"><?= htmlspecialchars($logName["name"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Data</label>
                <input type="date" name="date" class="border p-2 rounded">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Ação</label>
                <select name="action_type" class="border p-2 rounded">
                    <option value="">Todas</option>
                    <option value="create-account">Criar conta</option>
                    <option value="accept-document">Aceitar documento</option>
                    <option value="reject-document">Rejeitar documento</option>
                    <option value="invalidate-document">Invalidar documento</option>
                    <option value="validate-document">Validar documento</option>
                    <option value="edit-document">Editar documento</option>
                    <option value="annul-document">Anular documento</option>
                    <option value="addition-document">Fazer aditamento de um documento</option>
                    <option value="upload-document">Fazer upload de um documento</option>
                    <option value="deactivation-document">Desativar documento</option>
                    <option value="restore-document">Restaurar documento</option>
                    <option value="create-course">Criar curso</option>
                    <option value="delete-course">Eliminar curso</option>
                    <option value="edit-course">Editar curso</option>
                    <option value="deactivation-course">Desativar curso</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                <button type="submit"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Filtrar</button>
            </div>
        </form>

        <!-- Tabela de Atividades -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilizador</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Documento</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($log['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php switch ($log['action']) {
                                    case 'create-account':
                                        $action = 'Criar conta';
                                        break;
                                    case 'accept-document':
                                        $action = 'Aceitar documento';
                                        break;
                                    case 'reject-document':
                                        $action = 'Rejeitar documento';
                                        break;
                                    case 'invalidate-document':
                                        $action = 'Invalidar documento';
                                        break;
                                    case 'validate-document':
                                        $action = 'Validar documento';
                                        break;
                                    case 'edit-document':
                                        $action = 'Editar documento';
                                        break;
                                    case 'annul-document':
                                        $action = 'Anular documento';
                                        break;
                                    case 'addition-document':
                                        $action = 'Fazer aditamento de um documento';
                                        break;
                                    case 'upload-document':
                                        $action = 'Fazer upload de um documento';
                                        break;
                                    case 'deactivation-document':
                                        $action = 'Desativar documento';
                                        break;
                                    case 'restore-document':
                                        $action = 'Restaurar documento';
                                        break;
                                    case 'create-course':
                                        $action = 'Criar curso';
                                        break;
                                    case 'delete-course':
                                        $action = 'Eliminar curso';
                                        break;
                                    case 'edit-course':
                                        $action = 'Editar curso';
                                        break;
                                    case 'deactivation-course':
                                        $action = 'Desativar curso';
                                        break;
                                    default:
                                        $action = 'Não encontrado';

                                } ?>
                                <?= htmlspecialchars(string: $action) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= isset($log['final_document_id']) ?
                                    '<form method="POST" action="print-pdf" target="_blank">' .
                                    '<input type="hidden" name="final_document_id" value="' . $log["final_document_id"] . '">' .
                                    '<button type="submit" class="p-2 bg-gray-300 hover:bg-gray-200 cursor-pointer rounded-lg">Ver Documento</button>'
                                    : "N/A" ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

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
</div>