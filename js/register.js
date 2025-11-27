document.getElementById("registerForm").addEventListener("submit", function(e){

    let pass = document.getElementById("pass").value.trim();
    let pass2 = document.getElementById("pass2").value.trim();

    if(pass.length < 6){
        alert("A password deve ter pelo menos 6 caracteres.");
        e.preventDefault();
        return;
    }

    if(pass !== pass2){
        alert("As passwords não coincidem.");
        e.preventDefault();
        return;
    }

});

setTimeout(() => {
    const alerta = document.querySelector(".alerta-erro");
    if (alerta) {
        alerta.style.transition = "0.5s";
        alerta.style.opacity = "0";
        alerta.style.transform = "translateY(-10px)";
    }
}, 5000);


