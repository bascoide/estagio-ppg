// Função para alternar o dropdown
function toggleDropdown(id, button) {
    var submenu = document.getElementById(id);
    submenu.classList.toggle("hidden");

    var arrow = button.querySelector(".arrow");
    if (submenu.classList.contains("hidden")) {
        arrow.textContent = "▼"; // Fechado
    } else {
        arrow.textContent = "▲"; // Aberto
    }
}