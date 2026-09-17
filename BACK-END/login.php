<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    include("conexao.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $senha = $_POST['senha'];

        if (empty($email) || empty($senha)) {

            $resposta = [
                "success" => false,
                "message" => "Por favor, preencha todos os campos."
            ];

            echo json_encode($resposta);
            exit();
        }

        $sql = "SELECT * FROM usuarios WHERE email = '$email'";
        $resultado = mysqli_query($conn, $sql);

        if (!$resultado) {
            die("Erro na consulta: " . mysqli_error($conn));
        }

        if (mysqli_num_rows($resultado) == 1) {

            $usuario = mysqli_fetch_assoc($resultado);

            if (password_verify($senha, $usuario['senha_hash'])) {

                $_SESSION['usuario_id'] = $usuario['usuario_id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                $resposta = [
                    "success" => true,
                    "message" => "Login realizado com sucesso!"
                ];

                echo json_encode($resposta);
                exit();

            } else {

                $resposta = [
                    "success" => false,
                    "message" => "Senha incorreta"
                ];

                echo json_encode($resposta);
                exit();
            }

        } else {

            $resposta = [
                "success" => false,
                "message" => "E-mail não encontrado"
            ];

            echo json_encode($resposta);
            exit();
        }

    } else {

        header("Location: ../FRONT-END/login.html");
        exit();
    }
?>