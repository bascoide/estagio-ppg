<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Utilizador</title>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">Verificação de Conta</h1>
            
            <?php include "messageError.php"; ?>
            
            <div class="mt-6 text-center">
                <a href="/login" 
                   class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-200">
                    <span>Voltar ao login</span>
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</body>
</html>