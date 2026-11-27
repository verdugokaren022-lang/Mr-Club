const formPerfil = document.getElementById("formPerfil");
const mensaje = document.getElementById("mensaje");

function mostrarMensaje(texto, tipo = "success") {
    if (!mensaje) return;
    mensaje.className = `alert-${tipo}`;
    mensaje.textContent = texto;
}

async function solicitarApi(metodo, datos = null) {
    const opciones = {
        method: metodo,
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
    };

    if (datos) {
        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch("../Modelo/gestionarPerfil.php", opciones);

    if (respuesta.status === 401) {
        window.location.href = "Login.html";
        throw new Error("Sesión no iniciada");
    }

    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.success) {
        throw new Error(resultado.mensaje || "Error en la solicitud");
    }

    return resultado;
}

async function cargarPerfil() {
    try {
        const resultado = await solicitarApi("GET");
        const perfil = resultado.perfil;

        document.getElementById("documento").value = perfil.documento ?? "";
        document.getElementById("nombres").value = perfil.nombres ?? "";
        document.getElementById("apellidos").value = perfil.apellidos ?? "";
        document.getElementById("email").value = perfil.email ?? "";

        const avatar = document.getElementById("avatarInicial");
        if (avatar && perfil.nombres) {
            avatar.textContent = perfil.nombres.trim().charAt(0).toUpperCase();
        }
    } catch (error) {
        mostrarMensaje(error.message, "danger");
    }
}

if (formPerfil) {
    formPerfil.addEventListener("submit", async (evento) => {
        evento.preventDefault();

        const datos = {
            nombres: document.getElementById("nombres").value.trim(),
            apellidos: document.getElementById("apellidos").value.trim(),
            email: document.getElementById("email").value.trim(),
        };

        try {
            const resultado = await solicitarApi("PUT", datos);
            mostrarMensaje(resultado.mensaje, "success");
            await cargarPerfil();
        } catch (error) {
            mostrarMensaje(error.message, "danger");
        }
    });
}

document.addEventListener("DOMContentLoaded", cargarPerfil);