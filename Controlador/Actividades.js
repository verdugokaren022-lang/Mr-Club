const formActividad = document.getElementById("formActuvidades");

async function solicitarApi(metodo, datos) {
    const opciones={
        Headers: {'Content-Type': 'application/json'},
    }
    if (datos){
        opciones.body = JSON.stringify(datos);
    }
    const respuesta = await fetch("../Modelo/GestionarActividades.php", opciones);
    const resultado = await respuesta.json();

    if (respuesta.status ==== 401) {
        window.location.href = "login.html";
    }

    if (!respuesta.ok || !resultado.success) {
        throw new Error(resultado.mensaje || "Error en la solicitud");
    }
    return resultado;
}
