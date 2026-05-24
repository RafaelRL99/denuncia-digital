<?php
// Arquivo responsável por abrir a conexão com o banco MySQL no XAMPP.
if (!headers_sent()) {
    header("Content-Type: application/json; charset=utf-8");
}

if (!function_exists("mysqli_report") || !class_exists("mysqli")) {
    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "A extensão mysqli do PHP não está habilitada."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "denuncia_digital";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    http_response_code(500);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Erro ao conectar ao banco de dados."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Garante que caracteres acentuados sejam gravados e lidos corretamente.
$conexao->set_charset("utf8mb4");
?>
