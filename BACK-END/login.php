<?php
    require_once "seguranca.php";

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: ../FRONT-END/login.html");
        exit();
    }

    header("Content-Type: application/json; charset=UTF-8");

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        echo json_encode([
            "success" => false,
            "message" => "Por favor, preencha todos os campos."
        ]);
        exit();
    }

    if (login_bloqueado($email)) {
        echo json_encode([
            "success" => false,
            "message" => "Muitas tentativas erradas. Aguarde 15 minutos e tente novamente."
        ]);
        exit();
    }

    require_once "conexao.php";

    $resultado = executar($conn, "SELECT usuario_id, nome, senha_hash FROM usuarios WHERE email = ?", "s", [$email]);

    if (!$resultado) {
        echo json_encode([
            "success" => false,
            "message" => "Erro no servidor. Tente novamente mais tarde."
        ]);
        exit();
    }

    $usuario = mysqli_fetch_assoc($resultado);

    // Quando o e-mail não existe, confere a senha contra um hash qualquer.
    // Assim a resposta demora o mesmo tempo e não revela quais e-mails existem.
    $hash = $usuario['senha_hash'] ?? '$2y$10$imhAqr/bBDfg0vIzNZ.TH.dVIpTmHt3f3BpXj7VaxPQHHd7asXXke';
    $senha_correta = password_verify($senha, $hash);

    // Mesma mensagem para e-mail inexistente e senha errada
    if ($usuario && $senha_correta) {

        // Gera um novo ID de sessão (evita sequestro de sessão)
        session_regenerate_id(true);
        limpar_tentativas_login($email);

        $_SESSION['usuario_id'] = $usuario['usuario_id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        echo json_encode([
            "success" => true,
            "message" => "Login realizado com sucesso!"
        ]);
        exit();
    }

    registrar_falha_login($email);

    echo json_encode([
        "success" => false,
        "message" => "E-mail ou senha inválidos."
    ]);
?>
