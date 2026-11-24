<?php
require_once "seguranca.php";
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!csrf_valido()) {
        die("Requisição inválida. Recarregue a página e tente novamente.");
    }

    $usuario_id = $_SESSION['usuario_id'];

    // 1. Busca os dados atuais do banco como backup
    $res_busca = executar($conn, "SELECT * FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id]);
    $atual = $res_busca ? mysqli_fetch_assoc($res_busca) : null;

    if (!$atual) {
        die("Erro ao carregar os dados do usuário.");
    }

    // 2. Se o campo enviado estiver vazio, mantém o valor atual do banco de dados
    $campos = ['nome', 'email', 'rua', 'cep', 'numero_casa', 'bairro', 'cidade'];
    $novo = [];
    foreach ($campos as $campo) {
        $valor = trim($_POST[$campo] ?? '');
        $novo[$campo] = $valor !== '' ? $valor : $atual[$campo];
    }

    if (!filter_var($novo['email'], FILTER_VALIDATE_EMAIL)) {
        die("Digite um e-mail válido.");
    }

    // Não deixa usar um e-mail que já pertence a outra conta
    $res_email = executar($conn, "SELECT usuario_id FROM usuarios WHERE email = ? AND usuario_id <> ?", "si", [$novo['email'], $usuario_id]);
    if ($res_email && mysqli_num_rows($res_email) > 0) {
        die("Este e-mail já está sendo usado por outra conta.");
    }

    // 3. Executa a atualização
    $sql_update = "UPDATE usuarios SET
                    nome = ?,
                    email = ?,
                    rua = ?,
                    cep = ?,
                    numero_casa = ?,
                    bairro = ?,
                    cidade = ?
                   WHERE usuario_id = ?";

    $atualizado = executar($conn, $sql_update, "sssssssi", [
        $novo['nome'], $novo['email'], $novo['rua'], $novo['cep'],
        $novo['numero_casa'], $novo['bairro'], $novo['cidade'], $usuario_id
    ]);

    if ($atualizado) {
        $_SESSION['usuario_nome'] = $novo['nome']; // Atualiza o nome na sessão
        header("Location: perfil.php");
        exit();
    } else {
        echo "Erro ao atualizar os dados. Tente novamente mais tarde.";
    }
} else {
    header("Location: perfil.php");
    exit();
}
?>
