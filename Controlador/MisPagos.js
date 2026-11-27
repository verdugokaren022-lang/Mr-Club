document.addEventListener("DOMContentLoaded", cargarMisPagos);

function formatearPesos(valor) {
    return Number(valor || 0).toLocaleString("es-CO", {
        style: "currency",
        currency: "COP",
        maximumFractionDigits: 0
    });
}

async function cargarMisPagos() {
    const contenedor = document.getElementById("contenidoPagos") || document.querySelector("main") || document.body;

    try {
        const respuesta = await fetch("../Modelo/gestionarPagos.php", {
            credentials: "same-origin"
        });

        if (respuesta.status === 401) {
            window.location.href = "Login.html";
            return;
        }

        const data = await respuesta.json();
        if (!respuesta.ok || !data.success) {
            throw new Error(data.mensaje || "No se pudieron cargar tus pagos");
        }

        const prestamos = data.prestamos || [];
        const pagos = data.pagos || [];

        const totalPagado = pagos.reduce((acc, item) => {
            const monto = item.valorCuota ?? item.valorPago ?? item.monto ?? 0;
            return acc + Number(monto);
        }, 0);

        const elTotal = document.getElementById("totalPagado");
        if (elTotal) {
            elTotal.textContent = formatearPesos(totalPagado);
        }

        const tbodyPrestamos = document.getElementById("tablaMisPrestamos");
        if (tbodyPrestamos) {
            tbodyPrestamos.innerHTML = prestamos.length
                ? prestamos.map((pr) => `
                    <tr>
                        <td>${pr.idPrestamo}</td>
                        <td>${formatearPesos(pr.valorPrestamo)}</td>
                        <td>${pr.tasaInteres}%</td>
                        <td>${pr.numeroCuotas}</td>
                        <td>${pr.fechaPrestamo}</td>
                    </tr>
                `).join("")
                : '<tr><td colspan="5">No tienes préstamos registrados.</td></tr>';
        }

        const tbodyPagos = document.getElementById("tablaMisPagos");
        if (tbodyPagos) {
            tbodyPagos.innerHTML = pagos.length
                ? pagos.map((pg) => {
                    const monto = pg.valorCuota ?? pg.valorPago ?? pg.monto ?? 0;
                    const fecha = pg.fechaPago ?? pg.fecha_pago ?? pg.fecha ?? "-";
                    return `
                        <tr>
                            <td>${pg.idPrestamo ?? "-"}</td>
                            <td>${formatearPesos(monto)}</td>
                            <td>${fecha}</td>
                        </tr>
                    `;
                }).join("")
                : '<tr><td colspan="3">No hay pagos registrados aún.</td></tr>';
        }

        if (!tbodyPrestamos && !tbodyPagos && !elTotal) {
            const resumen = document.createElement("div");
            resumen.style.marginTop = "1rem";
            resumen.innerHTML = `
                <p><strong>Préstamos:</strong> ${prestamos.length} | <strong>Total pagado:</strong> ${formatearPesos(totalPagado)}</p>
            `;
            contenedor.appendChild(resumen);
        }
    } catch (error) {
        console.error("Error en MisPagos.js:", error);
        const mensaje = document.getElementById("mensajePagos");
        if (mensaje) {
            mensaje.textContent = error.message;
        }
    }
}