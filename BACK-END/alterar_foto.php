<?php

session_start();
include("conexao.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../FRONT-END/login.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if (isset($_FILES['nova_foto']) && $_FILES['nova_foto']['error'] == 0) {

    $nome_original = $_FILES['nova_foto']['name'];
    $temporario = $_FILES['nova_foto']['tmp_name'];
    $tamanho = $_FILES['nova_foto']['size'];

    $extensao = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extensao, $extensoes_permitidas)) {
        die("Formato de imagem não permitido.");
    }

    if ($tamanho > 5 * 1024 * 1024) {
        die("A foto deve ter no máximo 5 MB.");
    }

    if (!is_dir("uploads")) {
        mkdir("uploads", 0777, true);
    }

    // Pega a foto antiga
    $sql_buscar = "SELECT foto FROM usuarios WHERE usuario_id = '$usuario_id'";
    $resultado = mysqli_query($conn, $sql_buscar);
    $usuario = mysqli_fetch_assoc($resultado);

    // Cria um nome novo para a foto
    $novo_nome = uniqid() . "." . $extensao;

    $caminho = "uploads/" . $novo_nome;

    // Salva a nova foto
    if (move_uploaded_file($temporario, $caminho)) {

        $sql = "UPDATE usuarios 
                SET foto = '$caminho' 
                WHERE usuario_id = '$usuario_id'";

        if (mysqli_query($conn, $sql)) {

            // Apaga a foto antiga
            if (!empty($usuario['foto']) && file_exists($usuario['foto'])) {
                unlink($usuario['foto']);
            }

            header("Location: pagina-principal.php");
            exit();

        } else {

            echo "Erro ao atualizar a foto: " . mysqli_error($conn);

        }

    } else {

        echo "Erro ao salvar a nova foto.";

    }

} else {

    header("Location: pagina-principal.php");
    exit();

}

?>
```
