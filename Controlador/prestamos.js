document.addEventListener("DOMContentLoaded", () => {
    cargarAsociadosSelect();
});

async function cargarAsociadosSelect() {
    const select = document.getElementById("Asociado");
    if (!select) return;

    try {
        const respuesta = await fetch("../Modelo/listarAsociados.php", {
            credentials: "same-origin"
        });
        const data = await respuesta.json();

        if (!respuesta.ok || !data.success) {
            throw new Error(data.mensaje || "No se pudieron cargar los asociados");
        }

        select.innerHTML = '<option value="">Seleccione un asociado</option>';

        (data.asociados || []).forEach((asociado) => {
            const option = document.createElement("option");
            option.value = asociado.idAsociado;

            const nombreCompleto = [asociado.nombres, asociado.apellidos]
                .filter(Boolean)
                .join(" ")
                .trim();

            const detalleExtra = asociado.documento
                ? ` (${asociado.documento})`
                : (asociado.email ? ` (${asociado.email})` : "");

            option.textContent = nombreCompleto
                ? `${nombreCompleto}${detalleExtra}`
                : (asociado.email || `Asociado #${asociado.idAsociado}`);

            select.appendChild(option);
        });
    } catch (error) {
        console.error("Error al cargar asociados:", error);
        select.innerHTML = '<option value="">Error al cargar asociados</option>';
    }
}