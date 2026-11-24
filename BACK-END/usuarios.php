<?php
// Funções usadas por várias páginas para ler e validar dados dos usuários.
// Inclua depois do conexao.php: require_once "usuarios.php";

// Tamanho máximo de cada campo (igual ao tamanho das colunas no banco)
const LIMITES_CAMPOS = [
    'nome'        => 100,
    'email'       => 255,
    'rua'         => 150,
    'cep'         => 10,
    'numero_casa' => 10,
    'bairro'      => 100,
    'cidade'      => 100,
];

// Pega os campos do formulário já sem espaços nas pontas
function campos_do_formulario() {
    $dados = [];
    foreach (array_keys(LIMITES_CAMPOS) as $campo) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }
    return $dados;
}

// Confere campos obrigatórios, tamanhos e formato do e-mail.
// Retorna a mensagem de erro, ou null se estiver tudo certo.
function validar_dados_usuario($dados) {
    foreach (LIMITES_CAMPOS as $campo => $limite) {
        if ($dados[$campo] === '') {
            return "Por favor, preencha todos os campos.";
        }
        if (mb_strlen($dados[$campo]) > $limite) {
            return "O campo \"$campo\" pode ter no máximo $limite caracteres.";
        }
    }

    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
        return "Digite um e-mail válido.";
    }

    return null;
}

// Diz se o e-mail já pertence a outra conta
function email_em_uso($conn, $email, $ignorar_usuario_id = 0) {
    $resultado = executar($conn,
        "SELECT usuario_id FROM usuarios WHERE email = ? AND usuario_id <> ?",
        "si", [$email, $ignorar_usuario_id]);

    return $resultado && mysqli_num_rows($resultado) > 0;
}

// Caminho da foto do usuário (ou null se não tiver)
function foto_do_usuario($conn, $usuario_id) {
    $resultado = executar($conn, "SELECT foto FROM usuarios WHERE usuario_id = ?", "i", [$usuario_id]);
    $linha = $resultado ? mysqli_fetch_assoc($resultado) : null;
    return $linha['foto'] ?? null;
}

// Média, estado e quantidade dos registros de emoção do usuário.
// Retorna null se der erro no banco.
function resumo_emocoes($conn, $usuario_id) {
    $resultado = executar($conn,
        "SELECT AVG(emocao) AS media, COUNT(*) AS quantidade
           FROM registro_emocoes
          WHERE usuario_id = ?",
        "i", [$usuario_id]);

    if (!$resultado) {
        return null;
    }

    $dados = mysqli_fetch_assoc($resultado);
    $media = (float)$dados['media'];
    $quantidade = (int)$dados['quantidade'];

    if ($quantidade === 0) {
        return ['media' => "N/A", 'status' => "Nenhum registro ainda", 'quantidade' => 0];
    }

    if ($media >= 4.0) {
        $status = "Excelente! 😁";
    } elseif ($media >= 3.0) {
        $status = "Estável 🙂";
    } elseif ($media >= 2.0) {
        $status = "Atenção 😐";
    } else {
        $status = "Precisa de apoio 😔";
    }

    return [
        'media'      => number_format($media, 1, ',', '.'),
        'status'     => $status,
        'quantidade' => $quantidade,
    ];
}
