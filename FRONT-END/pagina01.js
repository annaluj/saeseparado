const menu = document.getElementById("menu");
const sidebar = document.getElementById("sidebar");
const fechar = document.getElementById("fechar");
const emojis = document.querySelectorAll(".emoji");
const mensagem = document.getElementById("mensagem");
const inputFoto = document.getElementById('input-foto');
const imgPreview = document.getElementById('imagem-preview');

if (inputFoto && imgPreview) {
    inputFoto.addEventListener('change', function(event) {
        const arquivo = event.target.files[0];
        if (arquivo) {
            const leitor = new FileReader();
            leitor.onload = function(e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
            }
            leitor.readAsDataURL(arquivo);
        }
    });
}
if(menu && sidebar){
    menu.addEventListener("click", function(){
        sidebar.classList.toggle("abrir");
    });
}

if(fechar && sidebar){
    fechar.addEventListener("click",function(){
        sidebar.classList.remove("abrir");
    });
}
if (emojis.length > 0 && mensagem) {
    emojis.forEach((emoji) => {
        emoji.addEventListener("click", (event) => {
            event.preventDefault();

            const valorEmocao = emoji.getAttribute('data-valor');

            if (!valorEmocao) {
                console.error("Atributo data-valor não encontrado no botão do emoji!");
                return;
            }

            emojis.forEach(e => e.classList.remove("ativo"));
            emoji.classList.add("ativo");

            mensagem.textContent = "Salvando...";
            mensagem.style.color = "#0056b3";
            mensagem.classList.add("mostrar");

            fetch('../BACK-END/registrar_emocao.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8'
                },
                body: JSON.stringify({ emocao: valorEmocao })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mensagem.textContent = "✅ " + data.message;
                    mensagem.style.color = "green";
                } else {
                    mensagem.textContent = "❌ " + data.message;
                    mensagem.style.color = "red";
                    console.log("Debug do servidor:", data.debug);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
                mensagem.textContent = "❌ Erro ao conectar ao servidor.";
                mensagem.style.color = "red";
            })
            .finally(() => {
                setTimeout(() => {
                    mensagem.classList.remove("mostrar");
                    mensagem.textContent = "";
                }, 3000);
            });
        });
    });
}

document.getElementById('input-foto').addEventListener('change', function(event) {
    const arquivo = event.target.files[0];
    const preview = document.getElementById('imagem-preview');

    if (arquivo) {
        const leitor = new FileReader();

        leitor.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block'; // Mostra a imagem assim que carregar
        };

        leitor.readAsDataURL(arquivo);
    } else {
        preview.src = '#';
        preview.style.display = 'none'; // Esconde se nenhum arquivo for selecionado
    }
});