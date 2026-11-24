// A data mínima do calendário já vem definida pelo PHP (agendar.php), no horário do Brasil.
const formConsulta = document.getElementById("formConsulta");

if (formConsulta) {
    formConsulta.addEventListener("submit", function(event){
        event.preventDefault();

        const btnSubmit = formConsulta.querySelector("button[type='submit']");
        btnSubmit.disabled = true;
        btnSubmit.textContent = "Agendando...";

        const dados = {
            data: document.getElementById("data").value,
            hora: document.getElementById("hora").value
        };

        fetch("../BACK-END/processar_agendamento.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken()
            },
            body: JSON.stringify(dados)
        })
        .then(resposta => resposta.json())
        .then(resultado => {
            if (resultado.success) {
                alert("✅ " + resultado.message + "\n\nAtenção: nossa equipe irá conferir se esse horário está disponível.");
                window.location.href = "../BACK-END/pagina-principal.php";
            } else {
                alert("⚠️ " + resultado.message);

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
