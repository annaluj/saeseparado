const formConsulta = document.getElementById("formConsulta");
const campoData = document.getElementById("data");
if (campoData) {
    const hoje = new Date().toISOString().split("T")[0];
    campoData.setAttribute("min", hoje);
}


if (formConsulta) {
    formConsulta.addEventListener("submit", function(event){
        event.preventDefault();

        const btnSubmit = formConsulta.querySelector("button[type='submit']");
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Agendando...";

        const dados = {
            nome: document.getElementById("nome").value,
            data: document.getElementById("data").value,
            hora: document.getElementById("hora").value
        };

        fetch("../BACK-END/processar_agendamento.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(dados)
        })
        .then(resposta => resposta.json())
        .then(resultado => {
            if (resultado.status === "sucesso") {
                alert("✅ " + resultado.mensagem + "\n\nAtenção: nossa equipe irá conferir se esse horário está disponível.");
                window.location.href = "../BACK-END/pagina-principal.php";
            } else {
                alert("⚠️ " + resultado.mensagem);

                btnSubmit.disabled = false;
                btnSubmit.textContent = "Agendar Consulta";
            }
        })
        .catch(erro => {
            console.error("Erro no envio:", erro);
            alert("❌ Erro ao enviar o agendamento. Verifique sua conexão.");
         
            btnSubmit.disabled = false;
            btnSubmit.textContent = "Agendar Consulta";
        });
    });
}
