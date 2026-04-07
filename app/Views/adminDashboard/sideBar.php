<div class="flex bg-gray-100 h-screen">
    <div class="w-64 bg-blue-900 text-white p-5 overflow-y-auto">
        <nav class="space-y-2">
            <div>
                <button onclick="toggleDropdown('protocol', this)"
                    class="w-full text-left cursor-pointer py-2 px-4 hover:bg-blue-700 rounded flex justify-between">
                    Protocolos <span class="arrow">▲</span>
                </button>
                <div id="protocol" class="pl-2">
                    <a href="/view-pending-documents" class="block py-2 px-4 hover:bg-blue-600 rounded">Pendentes</a>
                    <a href="/need-validation-documents" class="block py-2 px-4 hover:bg-blue-600 rounded">Por
                        validar</a>
                    <a href="/view-validation-documents" class="block py-2 px-4 hover:bg-blue-600 rounded">Validados</a>
                </div>
                <hr class="my-2 border-cyan-600">

                <button onclick="toggleDropdown('document', this)"
                    class="w-full text-left cursor-pointer py-2 px-4 hover:bg-blue-700 rounded flex justify-between">
                    Documentos <span class="arrow">▲</span>
                </button>
                <div id="document" class="pl-2">
                    <a href="/upload-document-form" class="block py-2 px-4 hover:bg-blue-600 rounded">Upload</a>
                    <a href="/show-documents" class="block py-2 px-4 hover:bg-blue-600 rounded">Consultar</a>
                </div>

                <hr class="my-2 border-cyan-600">
                <button onclick="toggleDropdown('register', this)"
                    class="w-full text-left cursor-pointer py-2 px-4 hover:bg-blue-700 rounded flex justify-between">
                    Cadastros <span class="arrow">▲</span>
                </button>
                <div id="register" class="pl-2">
                    <a href="/create-admin" class="block py-2 px-4 hover:bg-blue-600 rounded">Criar</a>
                    <a href="/show-users" class="block py-2 px-4 hover:bg-blue-600 rounded">Consultar</a>
                    <a href="/courses" class="block py-2 px-4 hover:bg-blue-600 rounded">Cursos</a>
                </div>
            </div>

            <hr class="my-2 border-cyan-600">
            <a href="/professor-search" class="block py-2 px-4 hover:bg-blue-600 rounded">Professores</a>

            <hr class="my-2 border-cyan-600">
            <a href="/admin-documentation" class="block py-2 px-4 hover:bg-blue-600 rounded">Documentação</a>

            <hr class="my-2 border-cyan-600">
            <a href="/admin-logs" class="block py-2 px-4 hover:bg-blue-600 rounded">Logs</a>
        </nav>
    </div>
</div>

<script src="/js/sidebar.js"></script>