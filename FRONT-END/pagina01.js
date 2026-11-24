const menu = document.getElementById("menu");
const sidebar = document.getElementById("sidebar");
const fechar = document.getElementById("fechar");
const emojis = document.querySelectorAll(".emoji");
const mensagem = document.getElementById("mensagem");
const inputFoto = document.getElementById('input-foto');
const imgPreview = document.getElementById('imagem-preview');
const novaFoto = document.getElementById('nova-foto');

// Cadastro: mostra a prévia da foto escolhida
if (inputFoto && imgPreview) {
    inputFoto.addEventListener('change', function(event) {
        const arquivo = event.target.files[0];
        if (arquivo) {
            const leitor = new FileReader();
            leitor.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block'; // Mostra a imagem assim que carregar
            }
            leitor.readAsDataURL(arquivo);
        } else {
            imgPreview.src = '#';
            imgPreview.style.display = 'none'; // Esconde se nenhum arquivo for selecionado
        }
    });
}

// Página principal: envia a nova foto assim que ela é escolhida
if (novaFoto) {
    novaFoto.addEventListener('change', function() {
        novaFoto.form.submit();
    });
}

// Index: abre e fecha o menu lateral
if (menu && sidebar) {
    menu.addEventListener("click", function() {
        sidebar.classList.toggle("abrir");
    });
}

if (fechar && sidebar) {
    fechar.addEventListener("click", function() {
        sidebar.classList.remove("abrir");
    });
}

// Página principal: atualiza a média, o estado e o total sem recarregar
function atualizarResumoEmocoes(resumo) {
    if (!resumo) {
        return;
    }
    document.getElementById("media-emocoes").textContent = resumo.media;
    document.getElementById("status-emocoes").textContent = resumo.status;
    document.getElementById("total-emocoes").textContent = resumo.quantidade;
}

// Página principal: registra a emoção clicada
if (emojis.length > 0 && mensagem) {
    emojis.forEach((emoji) => {
        emoji.addEventListener("click", (event) => {
            event.preventDefault();

            const valorEmocao = emoji.dataset.valor;

            emojis.forEach(e => {
                e.classList.remove("ativo");
                e.disabled = true; // evita cliques repetidos enquanto salva
            });
            emoji.classList.add("ativo");

            mensagem.textContent = "Salvando...";
            mensagem.style.color = "#0056b3";
            mensagem.classList.add("mostrar");

            fetch('../BACK-END/registrar_emocao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                    'X-CSRF-Token': csrfToken()
                },
                body: JSON.stringify({ emocao: valorEmocao })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mensagem.textContent = "✅ " + data.message;
                    mensagem.style.color = "green";
                    atualizarResumoEmocoes(data.resumo);
                } else {
                    mensagem.textContent = "❌ " + data.message;
                    mensagem.style.color = "red";
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                mensagem.textContent = "❌ Erro ao conectar ao servidor.";
                mensagem.style.color = "red";
            })
            .finally(() => {
                emojis.forEach(e => e.disabled = false);
                setTimeout(() => {
                    mensagem.classList.remove("mostrar");
                    mensagem.textContent = "";
                }, 3000);
            });
        });
    });
}
