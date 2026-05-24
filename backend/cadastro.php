<?php
// Endpoint único para cadastrar denúncias e devolver estatísticas ao dashboard.
header("Content-Type: application/json; charset=utf-8");

require_once "conexao.php";

$acao = $_POST["acao"] ?? $_GET["acao"] ?? "";

if ($acao === "cadastrar") {
    cadastrarDenuncia($conexao);
} elseif ($acao === "estatisticas") {
    listarEstatisticas($conexao);
} else {
    http_response_code(400);
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "Ação inválida."
    ], JSON_UNESCAPED_UNICODE);
}

$conexao->close();

// Valida os campos e grava a denúncia sem coletar identificação pessoal.
function cadastrarDenuncia(mysqli $conexao): void
{
    $tipo = trim($_POST["tipo"] ?? "");
    $plataforma = trim($_POST["plataforma"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $linkReferencia = trim($_POST["link_referencia"] ?? "");
    $consentimento = isset($_POST["consentimento"]) ? 1 : 0;

    if ($tipo === "" || $plataforma === "" || $descricao === "" || $consentimento !== 1) {
        http_response_code(422);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Preencha os campos obrigatórios e confirme as orientações de privacidade."
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if ($linkReferencia !== "" && !filter_var($linkReferencia, FILTER_VALIDATE_URL)) {
        http_response_code(422);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Informe um link válido ou deixe o campo em branco."
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    $sql = "INSERT INTO denuncias (tipo, plataforma, descricao, link_referencia, consentimento)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("ssssi", $tipo, $plataforma, $descricao, $linkReferencia, $consentimento);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Denúncia registrada com sucesso."
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            "sucesso" => false,
            "mensagem" => "Erro ao salvar a denúncia."
        ], JSON_UNESCAPED_UNICODE);
    }

    $stmt->close();
}

// Consulta os dados agregados que alimentam os gráficos do Chart.js.
function listarEstatisticas(mysqli $conexao): void
{
    $total = buscarTotal($conexao);
    $porTipo = buscarAgrupamento($conexao, "tipo");
    $porPlataforma = buscarAgrupamento($conexao, "plataforma");

    echo json_encode([
        "sucesso" => true,
        "total" => $total,
        "por_tipo" => $porTipo,
        "por_plataforma" => $porPlataforma
    ], JSON_UNESCAPED_UNICODE);
}

// Retorna o número total de denúncias cadastradas.
function buscarTotal(mysqli $conexao): int
{
    $resultado = $conexao->query("SELECT COUNT(*) AS total FROM denuncias");
    $linha = $resultado->fetch_assoc();

    return (int) $linha["total"];
}

// Retorna a quantidade de denúncias agrupada por uma coluna permitida.
function buscarAgrupamento(mysqli $conexao, string $coluna): array
{
    $colunasPermitidas = ["tipo", "plataforma"];

    if (!in_array($coluna, $colunasPermitidas, true)) {
        return [];
    }

    $sql = "SELECT $coluna AS rotulo, COUNT(*) AS total
            FROM denuncias
            GROUP BY $coluna
            ORDER BY total DESC, rotulo ASC";
    $resultado = $conexao->query($sql);
    $dados = [];

    while ($linha = $resultado->fetch_assoc()) {
        $dados[] = [
            "rotulo" => $linha["rotulo"],
            "total" => (int) $linha["total"]
        ];
    }

    return $dados;
}
?>
