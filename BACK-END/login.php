<?php
    require_once "seguranca.php";
    include("conexao.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        header("Content-Type: application/json; charset=UTF-8");

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';

        if (empty($email) || empty($senha)) {
            echo json_encode([
                "success" => false,
                "message" => "Por favor, preencha todos os campos."
            ]);
            exit();
        }

        $resultado = executar($conn, "SELECT usuario_id, nome, senha_hash FROM usuarios WHERE email = ?", "s", [$email]);

        if (!$resultado) {
            echo json_encode([
                "success" => false,
                "message" => "Erro no servidor. Tente novamente mais tarde."
            ]);
            exit();
        }

        $usuario = mysqli_fetch_assoc($resultado);

        // Mesma mensagem para e-mail inexistente e senha errada,
        // assim ninguém descobre quais e-mails estão cadastrados
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {

            // Gera um novo ID de sessão (evita sequestro de sessão)
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['usuario_id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            echo json_encode([
                "success" => true,
                "message" => "Login realizado com sucesso!"
            ]);
            exit();
        }

        echo json_encode([
            "success" => false,
            "message" => "E-mail ou senha inválidos."
        ]);
        exit();

    } else {

        header("Location: ../FRONT-END/login.html");
        exit();
    }
?>
