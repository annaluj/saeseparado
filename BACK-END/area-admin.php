<?php
require_once "seguranca.php";
require_once "conexao.php";
require_once "usuarios.php";
require_once "agendamentos.php";

exigir_tipo($conn, 'admin');

$usuario_id = $_SESSION['usuario_id'];

// Números do resumo
$resultado = executar($conn,
    "SELECT
        (SELECT COUNT(*) FROM usuarios WHERE tipo = 'estudante' AND ativo = 1) AS estudantes,
        (SELECT COUNT(*) FROM usuarios WHERE tipo = 'profissional' AND ativo = 1) AS profissionais,
        (SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente' AND data_consulta >= CURDATE()) AS pendentes,
        (SELECT COUNT(*) FROM agendamentos WHERE status = 'confirmado' AND data_consulta >= CURDATE()) AS confirmadas");
$numeros = $resultado ? mysqli_fetch_assoc($resultado) : [];

$pendentes = buscar_agendamentos($conn, "a.status = 'pendente' AND a.data_consulta >= CURDATE()");
$confirmadas = buscar_agendamentos($conn, "a.status = 'confirmado' AND a.data_consulta >= CURDATE()");
$profissionais = listar_profissionais($conn);

// Lista de usuários, com busca por nome ou e-mail
$busca = trim($_GET['busca'] ?? '');
$resultado = executar($conn,
    "SELECT usuario_id, nome, email, tipo, ativo FROM usuarios
      WHERE nome LIKE ? OR email LIKE ?
      ORDER BY FIELD(tipo, 'admin', 'profissional', 'estudante'), nome
      LIMIT 100",
    "ss", ["%$busca%", "%$busca%"]);
$usuarios = $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : [];

$foto = foto_do_usuario($conn, $usuario_id);
$subtitulo = "Área do administrador: distribua os pedidos e gerencie as contas do SAE.";

$nomes_tipo = ['estudante' => 'Estudante', 'profissional' => 'Profissional', 'admin' => 'Administrador'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Administrador - SAE</title>
    <link rel="stylesheet" href="../FRONT-END/pagina-principal.css">
    <link rel="stylesheet" href="../FRONT-END/areas.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Federo&display=swap" rel="stylesheet">
</head>
<body class="area-trabalho">

<div class="fundo">

    <?php include "cabecalho.php"; ?>

    <main class="cards">

        <!-- RESUMO -->
        <div class="card">
            <h2>Resumo</h2>
            <div class="resumo-numeros">
                <div class="numero-destaque">
                    <span class="numero"><?php echo (int)($numeros['estudantes'] ?? 0); ?></span>
                    <span class="rotulo-numero">Estudantes</span>
                </div>
                <div class="numero-destaque">
                    <span class="numero"><?php echo (int)($numeros['profissionais'] ?? 0); ?></span>
                    <span class="rotulo-numero">Profissionais</span>
                </div>
                <div class="numero-destaque">
                    <span class="numero"><?php echo (int)($numeros['pendentes'] ?? 0); ?></span>
                    <span class="rotulo-numero">Pedidos pendentes</span>
                </div>
                <div class="numero-destaque">
                    <span class="numero"><?php echo (int)($numeros['confirmadas'] ?? 0); ?></span>
                    <span class="rotulo-numero">Consultas marcadas</span>
                </div>
            </div>
        </div>

        <!-- PEDIDOS PENDENTES -->
        <div class="card">
            <h2>Pedidos de agendamento (<?php echo count($pendentes); ?>)</h2>
            <p>Escolha o profissional que vai atender e confirme.</p>

            <?php if (!$profissionais): ?>
                <p class="aviso aviso-erro">Ainda não há profissionais cadastrados. Transforme uma conta em profissional na lista de usuários abaixo.</p>
            <?php endif; ?>

            <?php if ($pendentes): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($pendentes as $a): ?>
                        <li class="agendamento">
                            👤 <strong>Estudante:</strong> <?php echo htmlspecialchars($a['estudante_nome']); ?><br>
                            📅 <strong>Data:</strong> <?php echo formatar_data($a['data_consulta']); ?>
                            às <?php echo formatar_hora($a['horario_consulta']); ?><br>
                            💙 <strong>Bem-estar:</strong> <?php echo bem_estar($a); ?>

                            <div class="acoes-agendamento">
                                <?php if ($profissionais): ?>
                                    <form action="acao_agendamento.php" method="POST">
                                        <?php echo csrf_campo(); ?>
                                        <input type="hidden" name="agendamento_id" value="<?php echo (int)$a['id']; ?>">
                                        <input type="hidden" name="acao" value="atribuir">
                                        <select name="profissional_id" required aria-label="Profissional">
                                            <option value="">Escolha o profissional</option>
                                            <?php foreach ($profissionais as $p): ?>
                                                <option value="<?php echo (int)$p['usuario_id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-color">✅ Confirmar</button>
                                    </form>
                                <?php endif; ?>
                                <?php botao_acao($a['id'], 'recusar', 'Recusar', 'btn-perigo', 'Recusar este pedido de agendamento?'); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Nenhum pedido aguardando confirmação. 🎉</p>
            <?php endif; ?>
        </div>

        <!-- CONSULTAS MARCADAS -->
        <div class="card">
            <h2>Consultas marcadas (<?php echo count($confirmadas); ?>)</h2>

            <?php if ($confirmadas): ?>
                <ul class="lista-agendamentos">
                    <?php foreach ($confirmadas as $a): ?>
                        <li class="agendamento">
                            📅 <strong><?php echo formatar_data($a['data_consulta']); ?></strong>
                            às <?php echo formatar_hora($a['horario_consulta']); ?><br>
                            👤 <strong>Estudante:</strong> <?php echo htmlspecialchars($a['estudante_nome']); ?><br>
                            🧑‍⚕️ <strong>Profissional:</strong> <?php echo htmlspecialchars($a['profissional_nome'] ?? 'Não definido'); ?>

                            <div class="acoes-agendamento">
                                <?php botao_acao($a['id'], 'concluir', '✔️ Concluída', 'btn-color'); ?>
                                <?php botao_acao($a['id'], 'cancelar', 'Cancelar consulta', 'btn-perigo', 'Cancelar esta consulta? O estudante verá que ela foi cancelada.'); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="vazio">Nenhuma consulta marcada.</p>
            <?php endif; ?>
        </div>

        <!-- USUÁRIOS -->
        <div class="card" id="usuarios">
            <h2>Usuários</h2>
            <p>Mude o tipo da conta ou desative quem não deve mais acessar o site.</p>

            <form action="area-admin.php#usuarios" method="GET" class="busca-usuarios">
                <input type="search" name="busca" placeholder="Buscar por nome ou e-mail" value="<?php echo htmlspecialchars($busca); ?>">
                <button type="submit" class="btn-color">Buscar</button>
            </form>

            <div class="tabela-rolagem">
                <table class="tabela-usuarios">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Tipo de conta</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $eu_mesmo = (int)$u['usuario_id'] === (int)$usuario_id; ?>
                            <tr class="<?php echo $u['ativo'] ? '' : 'usuario-inativo'; ?>">
                                <td><?php echo htmlspecialchars($u['nome']); ?><?php echo $eu_mesmo ? ' <small>(você)</small>' : ''; ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($eu_mesmo): ?>
                                        <?php echo $nomes_tipo[$u['tipo']]; ?>
                                    <?php else: ?>
                                        <form action="acao_usuario.php" method="POST" class="form-linha"
                                              data-confirmar="Mudar o tipo da conta de <?php echo htmlspecialchars($u['nome']); ?>?">
                                            <?php echo csrf_campo(); ?>
                                            <input type="hidden" name="usuario_id" value="<?php echo (int)$u['usuario_id']; ?>">
                                            <input type="hidden" name="acao" value="mudar_tipo">
                                            <select name="tipo" aria-label="Tipo de conta">
                                                <?php foreach ($nomes_tipo as $valor => $nome): ?>
                                                    <option value="<?php echo $valor; ?>" <?php echo $u['tipo'] === $valor ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn-color btn-pequeno">Salvar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($eu_mesmo): ?>
                                        Ativa
                                    <?php elseif ($u['ativo']): ?>
                                        <form action="acao_usuario.php" method="POST"
                                              data-confirmar="Desativar a conta de <?php echo htmlspecialchars($u['nome']); ?>? A pessoa não vai mais conseguir entrar.">
                                            <?php echo csrf_campo(); ?>
                                            <input type="hidden" name="usuario_id" value="<?php echo (int)$u['usuario_id']; ?>">
                                            <input type="hidden" name="acao" value="desativar">
                                            <button type="submit" class="btn-perigo btn-pequeno">Desativar</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="acao_usuario.php" method="POST">
                                            <?php echo csrf_campo(); ?>
                                            <input type="hidden" name="usuario_id" value="<?php echo (int)$u['usuario_id']; ?>">
                                            <input type="hidden" name="acao" value="ativar">
                                            <button type="submit" class="btn-color btn-pequeno">Reativar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$usuarios): ?>
                <p class="vazio">Nenhum usuário encontrado.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Meu perfil</h2>
            <p>Consulte e altere seus dados pessoais.</p>
            <button type="button" class="btn-color" data-href="perfil.php">Ver meu perfil</button>
        </div>

    </main>

</div>

<script src="../FRONT-END/comum.js"></script>
<script src="../FRONT-END/pagina01.js"></script>

</body>
</html>
