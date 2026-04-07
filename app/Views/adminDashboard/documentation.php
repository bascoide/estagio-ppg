<?php require 'sideBar.php'; ?>

<div class="flex-grow h-screen p-4 bg-white rounded shadow-md overflow-y-scroll">

    <h1 id="topo" class="text-3xl font-bold text-blue-800 mb-6">Documentação da Interface de Administração</h1>

    <h2 class="text-xl font-bold text-blue-700 mt-8 mb-2">Índice</h2>
    <ul class="list-disc ml-6 mb-6 text-blue-600">
        <li><a href="#status" class="hover:underline">1. Status dos Documentos</a></li>
        <li><a href="#sidebar" class="hover:underline">2. Barra lateral</a></li>
        <ul class="list-disc ml-6 mb-6 text-blue-600">
            <li><a href="#protocolos" class="hover:underline">2.1. Protocolos</a></li>
            <li><a href="#documentos" class="hover:underline">2.2. Documentos</a></li>
            <li><a href="#cadastros" class="hover:underline">2.3. Cadastros</a></li>
            <li><a href="#professores" class="hover:underline">2.4. Professores</a></li>
        </ul>
        <label class="text-gray-700">Parte mais técnica: </label>
        <li><a href="#preenchimento" class="hover:underline">3. Regras de preenchimento de documentos</a></li>
        <ul class="list-disc ml-6 mb-6 text-blue-600">
            <li><a href="#tipos-campos" class="hover:underline">3.1. Tipos de campos</a></li>
            <li><a href="#syntax" class="hover:underline">3.2. Sintaxe</a></li>
        </ul>
    </ul>

    <hr class="my-6 border-gray-300">

    <h1 id="status" class="text-xl font-bold text-gray-800 mt-8 mb-2">1. Status dos Documentos</h1>
    <ul class="list-disc ml-6 mb-6">
        <li><span class="text-yellow-600 font-semibold">Pendente</span> – O documento está aguardando revisão ou ação do
            administrador.</li>
        <li><span class="text-red-500 font-semibold">Recusado</span> – O documento foi rejeitado e precisa ser revisto
            pelo aluno.</li>
        <li><span class="text-green-500 font-semibold">Aceito</span> – O documento foi aprovado e precisa ser assinado
            pelo aluno.</li>
        <li><span class="text-yellow-800 font-semibold">Por validar</span> – O documento foi reenviado e precisa ser
            revisto para prosseguir.</li>
        <li><span class="text-purple-800 font-semibold">Invalidado</span> – O documento está mal formatado ou não foi
            assinado.</li>
        <li><span class="text-cyan-500 font-semibold">Validado</span> – O documento está finalizado.</li>
        <li><span class="text-gray-600 font-semibold">Inativo</span> – Apenas usados para manter histórico, estes são documentos que foram editados anteriormente e não estão disponíveis para edição. Eles são visíveis apenas ao consultar um aluno, desde que seja especificado no filtro.</li>


    </ul>

    <div class="text-right text-sm text-blue-500 hover:underline mb-8">
        <a href="#topo">⬆ Voltar ao topo</a>
    </div>

    <h1 id="sidebar" class="text-xl font-bold text-gray-800 mt-8 mb-2">2. Barra lateral</h1>

    <div class="ml-8">
        <h2 id="protocolos" class="text-xl font-bold text-blue-700 mt-8 mb-2">2.1. Protocolos</h2>
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mb-4">
            <p>Gestão de protocolos com diferentes estados e ações administrativas.</p>
        </div>
        <h3 class="font-semibold text-gray-700">Estados:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li><strong>Pendentes</strong>: Aguardando revisão.
                <ul class="list-disc ml-6">
                    <li>Transições: <em>Aceite</em> ou <em>Recusado</em></li>
                </ul>
            </li>
            <li><strong>Por validar</strong>: Verificação de assinaturas e formatação.
                <ul class="list-disc ml-6">
                    <li>Transições: <em>Validado</em> ou <em>Invalidado</em></li>
                </ul>
            </li>
            <li><strong>Validados</strong>: Processo concluído.
                <ul class="list-disc ml-6">
                    <li>É possível anular este protocolo, caso necessário.</li>
                </ul>
            </li>
        </ul>

        <h3 class="font-semibold text-gray-700">Funcionalidades:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Download e impressão.</li>
            <li>Visualizar planos associados (vermelho = não visualizado, verde = visualizado).</li>
            <div class="grid grid-cols-2 w-full mb-4">
                <img src="/images/documentation/Plan_red.png" alt="Plano não visualizado">
                <img src="/images/documentation/Plan_green.png" alt="Plano visualizado">
            </div>
            <li>Edição de dados nos <strong>Pendentes</strong> (ao clicar no nome da pessoa):</li>
            <ul class="list-disc ml-6 mb-4">
                <li>Escrever o motivo da rejeição, caso rejeite.</li>
                <li>Marcar campos inválidos através da caixa de seleção (checkbox) no campo correspondente, caso se aplique.</li>
            </ul>
            <li>Validar documentos nos <strong>Por validar</strong></li>
            <ul class="list-disc ml-6 mb-4">
                <li>Escolher o email (presidência) desejado.</li>
                <li>Validar irá enviar um email para o <strong>email escolhido</strong>, para que possa ser assinado pelo mesmo.</li>
                <li>Ao clicar na caixa de opções, pode adicionar e retirar emails (presidência).</li>
                <img src="/images/documentation/box_options.png" alt="Caixa de opções">
            </ul>
            <li>Ver quantidade de documentos nos <strong>Validados</strong></li>
            <ul class="list-disc ml-6 mb-4">
                <li>Através de um curso e um ano (letivo ou civil), aparecerá a quantidade relacionada.</li>
                <li>Escolha o tipo de ano desejado.</li>
            </ul>
            <li>Fazer aditamento de um documento nos <strong>Validados</strong></li>
            <ul class="list-disc ml-6 mb-4">
                <li>Ao fazer um aditamento, é possível ver os anteriores.</li>
                <li>E realizar novos, se necessário.</li>
            </ul>
            <li>Filtrar os documentos pelo ano letivo nos <strong>Validados</strong></li>
            <ul class="list-disc ml-6 mb-4">
                <li>Os protocolos mostrados são do ano letivo mais atual.</li>
                <li>Sendo possível mudar o ano letivo através do botão correspondente (no "Gestão de Protocolos").</li>
            </ul>
        </ul>

        <div class="text-right text-sm text-blue-500 hover:underline mb-8">
            <a href="#topo">⬆ Voltar ao topo</a>
        </div>

        <h2 id="documentos" class="text-xl font-bold text-blue-700 mt-8 mb-2">2.2. Documentos</h2>
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mb-4">
            <p>Área para gestão de documentos submetidos pelo administrador.</p>
        </div>
        <h3 class="font-semibold text-gray-700">Upload:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Submissão de documentos <code>.docx</code>.</li>
            <li>Classificação como "Plano" ou "Protocolo".</li>
            <li>Associação com tipos de cursos.</li>
        </ul>

        <h3 class="font-semibold text-gray-700">Consultar:</h3>
        <ul class="list-disc ml-6 mb-4">
            <span class="font-semibold">Visualizar documentos submetidos (upload).</span>
            <li>Visualizar documento limpo (sem campos personalizados).</li>
            <img src="/images/documentation/Documentos_print.png" alt="Visualizar documento limpo">
            <li>Fazer download.</li>
            <img src="/images/documentation/Documentos_transf.png" alt="Transferência de documentos">
            <li>Eliminar (soft delete).</li>
            <img src="/images/documentation/Documentos_funcs.png" alt="Funções de documentos">
            <li>Recuperar documentos eliminados.</li>
            <img src="/images/documentation/Documentos_funcs2.png" alt="Recuperação de documentos">
        </ul>

        <div class="bg-gray-100 border-l-4 p-4 rounded mb-4 font-mono text-sm text-gray-700">
            Submeter Documento → Gerar Formulário para Alunos → Gerir Documento (Ver/Transferir/Eliminar/Recuperar)
            <br>
            Para mais detalhes técnicos, consulte a seção <a href="#preenchimento" class="text-blue-600 hover:underline">Regras de preenchimento de documentos</a>.
        </div>

        <div class="text-right text-sm text-blue-500 hover:underline mb-8">
            <a href="#topo">⬆ Voltar ao topo</a>
        </div>

        <h2 id="cadastros" class="text-xl font-bold text-blue-700 mt-8 mb-2">2.3. Cadastros</h2>
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mb-4">
            <p>Gestão de administradores, cursos e alunos.</p>
        </div>
        <h3 class="font-semibold text-gray-700">Criar:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Criar novas contas de administradores.</li>
        </ul>

        <h3 class="font-semibold text-gray-700">Consultar:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Acessar e pesquisar contas de alunos.</li>
            <li>Filtrar por diversos critérios.</li>
            <li>Interagir com documentos dos alunos.
                <ul class="list-disc ml-6">
                    <li>Visualizar campos (editar se estiver pendente) - ao clicar no documento.</li>
                    <li>Visualizar documentos completos (impressão).</li>
                </ul>
            </li>
            <li>Ao clicar em um protocolo aceito, é possível anulá-lo.</li>
        </ul>

        <h3 class="font-semibold text-gray-700">Cursos:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Criar, editar e eliminar cursos.</li>
        </ul>

        <div class="text-right text-sm text-blue-500 hover:underline">
            <a href="#topo">⬆ Voltar ao topo</a>
        </div>

        <h2 id="professores" class="text-xl font-bold text-blue-700 mt-8 mb-2">2.4. Professores</h2>
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mb-4">
            <p>Gestão de documentos (relatórios) relacionados aos professores.</p>
        </div>

        <h3 class="font-semibold text-gray-700">Visualização:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Consultar professores.</li>
            <li>Pesquisar por nome ou curso.</li>
        </ul>

        <h3 class="font-semibold text-gray-700">Relatórios:</h3>
        <ul class="list-disc ml-6 mb-4">
            <li>Através do curso e do ano letivo, é gerado um relatório de estágio.</li>
            <li>Visualizar e transferir relatórios.</li>
            <li>É transferido um arquivo <strong>.zip</strong>, que deve ser extraído. Clique com o botão direito do rato no arquivo e selecione "Extrair".</li>
        </ul>

        <div class="text-right text-sm text-blue-500 hover:underline mb-8">
            <a href="#topo">⬆ Voltar ao topo</a>
        </div>
    </div>

    <h1 id="preenchimento" class="text-xl font-bold text-gray-800 mt-8 mb-2">3. Regras de preenchimento de documentos</h1>
    Esta seção é destinada a utilizadores mais técnicos e refere-se à parte de upload de documentos <code>.docx</code>.
    <div class="ml-8">
        <h2 id="tipos-campos" class="text-xl font-bold text-blue-700 mt-8 mb-2">3.1. Tipos de campos</h2>
        Existem campos que usam atributos do HTML, os quais geram o que sua tag HTML correspondente gera. Tipos de campos com equivalentes HTML disponíveis:
        <ul class="list-disc ml-6 mb-4">
            <li>text</li>
            <li>date</li>
            <li>number</li>
            <li>title</li>
            <li>checkbox</li>
            <li>email</li>
            <li>radio</li>
            <li>time</li>
            <li>tel</li>
        </ul>
        Mas também existem tipos de campos personalizados como:
        <ul class="list-disc ml-6 mb-4">
            <li>nif (um campo de texto que apenas permite NIFs válidos)</li>
            <li>nipc (um campo de texto que apenas permite NIPCs válidos)</li>
            <li>group (não altera o aspecto da página mas divide checkboxes e botões de rádio em grupos diferentes)</li>
            <li>course (obtém automaticamente o curso do aluno)</li>
            <li>professor (um campo de texto que ajuda a mostrar ao aluno todos os professores do seu curso)</li>
            <li>NA start (define uma zona onde os campos são considerados não aplicáveis)</li>
            <li>NA end (define onde a zona de campos não aplicáveis termina, (nota: não colocar o nome igual ao NA start))</li>
        </ul>
    <div class="text-right text-sm text-blue-500 hover:underline mb-8">
        <a href="#topo">⬆ Voltar ao topo</a>
    </div>
        <h2 id="syntax" class="text-xl font-bold text-blue-700 mt-8 mb-2">3.2. Sintaxe</h2>
        Ao editar o documento, quando precisar adicionar um campo, este deverá ter a seguinte sintaxe:
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mt-2 mb-4">
            <p>{ nome do campo | tipo do campo }</p>
        </div>
        <div class="bg-blue-100 border-l-4 border-blue-400 text-blue-800 p-4 rounded mb-4">
            <p>Caso um campo tenha o nome igual a outro, um deles será ignorado no formulário e ambos iram ter o mesmo valor no document final</p>
        </div>
        Qualquer espaço adicional será ignorado.

    <div class="text-right text-sm text-blue-500 hover:underline mb-8">
        <a href="#topo">⬆ Voltar ao topo</a>
    </div>
    </div>
</div>
