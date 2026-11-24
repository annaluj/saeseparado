<?php
// Funções dos agendamentos, usadas pelas áreas do estudante, do profissional e do administrador.
// Inclua depois do conexao.php: require_once "agendamentos.php";

const STATUS_AGENDAMENTO = [
    'pendente'   => 'Aguardando confirmação',
    'confirmado' => 'Confirmado',
    'recusado'   => 'Recusado',
    'concluido'  => 'Concluído',
    'cancelado'  => 'Cancelado',
];

// Etiqueta colorida do status (as cores ficam no areas.css)
function etiqueta_status($status) {
    $texto = STATUS_AGENDAMENTO[$status] ?? $status;
    return '<span class="status status-' . htmlspecialchars($status) . '">' . htmlspecialchars($texto) . '</span>';
}

// Média de emoções do estudante, para o profissional ter uma noção de como ele está
function bem_estar($agendamento) {
    if (!$agendamento['emocao_quantidade']) {
        return "sem registros";
    }
    return number_format($agendamento['emocao_media'], 1, ',', '.') . " / 5.0 ("
        . (int)$agendamento['emocao_quantidade'] . " registros)";
}

// Formulário de um botão que muda o agendamento (confirmar, recusar, concluir, cancelar).
// Envia para acao_agendamento.php. $confirmacao: pergunta mostrada antes de enviar.
function botao_acao($agendamento_id, $acao, $texto, $classe, $confirmacao = null) {
    $confirmar = $confirmacao ? ' data-confirmar="' . htmlspecialchars($confirmacao) . '"' : '';
    echo '<form action="acao_agendamento.php" method="POST"' . $confirmar . '>'
        . csrf_campo()
        . '<input type="hidden" name="agendamento_id" value="' . (int)$agendamento_id . '">'
        . '<input type="hidden" name="acao" value="' . htmlspecialchars($acao) . '">'
        . '<button type="submit" class="' . htmlspecialchars($classe) . '">' . htmlspecialchars($texto) . '</button>'
        . '</form>';
}

function formatar_data($data) {
    return date('d/m/Y', strtotime($data));
}

function formatar_hora($hora) {
    return date('H:i', strtotime($hora));
}

// Busca agendamentos com o nome do estudante, do profissional e a média de emoções do estudante.
// $filtro é o trecho do WHERE escrito no código (nunca com dados do usuário);
// os valores entram por "?" em $valores.
// Ex.: buscar_agendamentos($conn, "a.status = ?", "s", ['pendente']);
function buscar_agendamentos($conn, $filtro, $tipos = "", $valores = [], $mais_recentes_primeiro = false, $limite = 100) {
    $ordem = $mais_recentes_primeiro ? "DESC" : "ASC";
    $limite = (int)$limite;

    $sql = "SELECT a.id, a.data_consulta, a.horario_consulta, a.status, a.profissional_id,
                   e.nome AS estudante_nome, e.email AS estudante_email,
                   p.nome AS profissional_nome,
                   em.media AS emocao_media, em.quantidade AS emocao_quantidade
              FROM agendamentos a
              JOIN usuarios e ON e.usuario_id = a.usuario_id
         LEFT JOIN usuarios p ON p.usuario_id = a.profissional_id
         LEFT JOIN (SELECT usuario_id, AVG(emocao) AS media, COUNT(*) AS quantidade
                      FROM registro_emocoes GROUP BY usuario_id) em ON em.usuario_id = a.usuario_id
             WHERE $filtro
          ORDER BY a.data_consulta $ordem, a.horario_consulta $ordem
             LIMIT $limite";

    $resultado = executar($conn, $sql, $tipos, $valores);
    return $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
}

// Profissionais ativos (para o administrador escolher quem atende)
function listar_profissionais($conn) {
    $resultado = executar($conn,
        "SELECT usuario_id, nome FROM usuarios WHERE tipo = 'profissional' AND ativo = 1 ORDER BY nome");
    return $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];
}

// O profissional já tem consulta confirmada nesse dia e horário?
function profissional_ocupado($conn, $profissional_id, $data, $hora, $ignorar_agendamento_id) {
    $resultado = executar($conn,
        "SELECT id FROM agendamentos
          WHERE profissional_id = ? AND status = 'confirmado'
            AND data_consulta = ? AND horario_consulta = ? AND id <> ?",
        "issi", [$profissional_id, $data, $hora, $ignorar_agendamento_id]);
    return $resultado && mysqli_num_rows($resultado) > 0;
}

// Muda a situação de um agendamento, conferindo quem pode fazer o quê.
//  - confirmar: profissional assume um pedido pendente
//  - atribuir:  administrador confirma um pedido pendente com um profissional escolhido
//  - recusar:   pedido pendente é recusado (profissional ou administrador)
//  - concluir / cancelar: consulta confirmada (o próprio profissional ou o administrador)
// Retorna a mensagem de erro, ou null se deu certo.
function mudar_agendamento($conn, $tipo_usuario, $usuario_id, $agendamento_id, $acao, $profissional_escolhido = 0) {
    $resultado = executar($conn,
        "SELECT id, status, profissional_id, data_consulta, horario_consulta FROM agendamentos WHERE id = ?",
        "i", [$agendamento_id]);
    $agendamento = $resultado ? mysqli_fetch_assoc($resultado) : null;

    if (!$agendamento) {
        return "Agendamento não encontrado.";
    }

    $eh_admin = $tipo_usuario === 'admin';
    $eh_dono = (int)$agendamento['profissional_id'] === (int)$usuario_id;
    $status = $agendamento['status'];

    switch ($acao) {
        case 'confirmar':
            if ($tipo_usuario !== 'profissional' || $status !== 'pendente') {
                return "Este pedido não pode mais ser confirmado.";
            }
            $novo_status = 'confirmado';
            $profissional = $usuario_id;
            break;

        case 'atribuir':
            if (!$eh_admin || $status !== 'pendente') {
                return "Este pedido não pode mais ser confirmado.";
            }
            $ids_validos = array_column(listar_profissionais($conn), 'usuario_id');
            if (!in_array((int)$profissional_escolhido, array_map('intval', $ids_validos), true)) {
                return "Escolha um profissional para atender.";
            }
            $novo_status = 'confirmado';
            $profissional = (int)$profissional_escolhido;
            break;

        case 'recusar':
            if ($status !== 'pendente') {
                return "Este pedido não pode mais ser recusado.";
            }
            $novo_status = 'recusado';
            $profissional = $eh_admin ? $agendamento['profissional_id'] : $usuario_id;
            break;

        case 'concluir':
        case 'cancelar':
            if ($status !== 'confirmado' || (!$eh_admin && !$eh_dono)) {
                return "Esta consulta não pode ser alterada.";
            }
            $novo_status = $acao === 'concluir' ? 'concluido' : 'cancelado';
            $profissional = $agendamento['profissional_id'];
            break;

        default:
            return "Ação inválida.";
    }

    if ($novo_status === 'confirmado'
        && profissional_ocupado($conn, $profissional, $agendamento['data_consulta'], $agendamento['horario_consulta'], $agendamento_id)) {
        return "Já existe uma consulta confirmada com esse profissional nesse dia e horário.";
    }

    // "AND status = ?" garante que ninguém mudou o agendamento entre a leitura e a gravação
    $ok = executar($conn,
        "UPDATE agendamentos SET status = ?, profissional_id = ? WHERE id = ? AND status = ?",
        "siis", [$novo_status, $profissional, $agendamento_id, $status]);

    if (!$ok) {
        return "Erro ao salvar. Tente novamente mais tarde.";
    }
    if (linhas_alteradas() === 0) {
        return "Este agendamento acabou de ser alterado por outra pessoa. Confira a lista atualizada.";
    }
    return null;
}
