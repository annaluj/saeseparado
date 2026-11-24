<?php
require_once "seguranca.php";
exigir_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: perfil.php");
    exit();
}

if (!csrf_valido()) {
    die("Requisição inválida. Recarregue a página e tente novamente.");
}

require_once "conexao.php";
require_once "usuarios.php";

$usuario_id = $_SESSION['usuario_id'];

// Todos os campos são obrigatórios, igual ao cadastro
$dados = campos_do_formulario();

$erro = validar_dados_usuario($dados);
if ($erro) {
    die($erro);
}

// Não deixa usar um e-mail que já pertence a outra conta
if (email_em_uso($conn, $dados['email'], $usuario_id)) {
    die("Este e-mail já está sendo usado por outra conta.");
}

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
    $dados['nome'], $dados['email'], $dados['rua'], $dados['cep'],
    $dados['numero_casa'], $dados['bairro'], $dados['cidade'], $usuario_id
]);

if ($atualizado) {
    $_SESSION['usuario_nome'] = $dados['nome']; // Atualiza o nome na sessão
    header("Location: perfil.php");
    exit();
}

echo "Erro ao atualizar os dados. Tente novamente mais tarde.";
?>
