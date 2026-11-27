document.addEventListener("DOMContentLoaded", () => {
    cargarAsociadosSelect();
    cargarPrestamosTabla();

    const form = document.getElementById("FormularioPrestamo");
    if (form) {
        form.addEventListener("submit", registrarPrestamo);
    }
});

function mostrarMensajePrestamo(texto, esError = false) {
    const el = document.getElementById("mensajePrestamo");
    if (!el) return;
    el.textContent = texto;
    el.style.color = esError ? "#b91c1c" : "#15803d";
}

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

async function registrarPrestamo(evento) {
    evento.preventDefault();

    const datos = {
        idAsociado: document.getElementById("Asociado").value,
        valorPrestamo: document.getElementById("ValorPrestamo").value,
        fechaPrestamo: document.getElementById("FechaPrestamo").value,
        tasaInteres: document.getElementById("Interes").value,
        numeroCuotas: document.getElementById("Cuotas").value,
        observaciones: document.getElementById("Observaciones")?.value.trim() || ""
    };

    try {
        const respuesta = await fetch("../Modelo/gestionarPrestamos.php", {
            method: "POST",
            credentials: "same-origin",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datos)
        });
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || "No se pudo registrar el préstamo");
        }

        mostrarMensajePrestamo(resultado.mensaje, false);
        evento.target.reset();
        await cargarPrestamosTabla();
    } catch (error) {
        mostrarMensajePrestamo(error.message, true);
    }
}

async function cargarPrestamosTabla() {
    const tbody = document.getElementById("tablaPrestamos");
    if (!tbody) return;

    try {
        const respuesta = await fetch("../Modelo/gestionarPrestamos.php", {
            credentials: "same-origin"
        });
        const resultado = await respuesta.json();

        if (!respuesta.ok || !resultado.success) {
            throw new Error(resultado.mensaje || "No se pudieron cargar los préstamos");
        }

        tbody.innerHTML = "";
        const lista = resultado.prestamos || [];

        if (lista.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">No hay préstamos registrados.</td></tr>';
            return;
        }

        lista.forEach((p) => {
            const nombreAsociado = [p.nombres, p.apellidos].filter(Boolean).join(" ").trim() || p.documento;
            const saldo = Number(p.saldoPendiente ?? p.valorPrestamo);
            const estado = saldo <= 0 ? "Pagado" : "Activo";

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${p.idPrestamo}</td>
                <td>${nombreAsociado}</td>
                <td>${Number(p.valorPrestamo).toLocaleString("es-CO", { style: "currency", currency: "COP", maximumFractionDigits: 0 })}</td>
                <td>${p.fechaPrestamo}</td>
                <td>${p.numeroCuotas}</td>
                <td>${saldo.toLocaleString("es-CO", { style: "currency", currency: "COP", maximumFractionDigits: 0 })}</td>
                <td>${estado}</td>
            `;
            tbody.appendChild(tr);
        });
    } catch (error) {
        console.error("Error al listar préstamos:", error);
        tbody.innerHTML = '<tr><td colspan="7">Error al cargar préstamos.</td></tr>';
    }
}