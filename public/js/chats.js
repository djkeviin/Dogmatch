// /public/js/chat.js
document.getElementById("chatForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const input = document.getElementById("mensaje");
    const texto = input.value.trim();

    if (texto !== "") {
        const contenedor = document.getElementById("chatMessages");
        const nuevo = document.createElement("div");
        nuevo.className = "mensaje enviado";
        nuevo.innerHTML = `<p>${texto}</p>`;
        contenedor.appendChild(nuevo);
        input.value = "";
        contenedor.scrollTop = contenedor.scrollHeight;
    }
});
