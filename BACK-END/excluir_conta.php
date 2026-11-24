<?php

require_once "seguranca.php";
exigir_login();

// Só aceita exclusão enviada pelo formulário do perfil (POST + token)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header("Location: perfil.php");
    exit();
}

require_once "conexao.php";
require_once "usuarios.php";

$usuario_id = $_SESSION['usuario_id'];

// Guarda a foto para apagar depois
$foto = foto_do_usuario($conn, $usuario_id);

// Exclui o usuário do banco (agendamentos e emoções são apagados junto pelo ON DELETE CASCADE)
if (executar($conn, "DELETE FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id])) {

    apagar_foto($foto);

    // Apaga as informações da sessão
    session_unset();
    session_destroy();

    // Volta para a página inicial
    header("Location: ../FRONT-END/index.html");
    exit();
}

echo "Erro ao excluir a conta. Tente novamente mais tarde.";

?>
