const formulario = document.getElementById('formuRegistro');

formulario.addEventListener("submit", async function (event) {

    // Evita recargar la pagina al enviar el formulario.
    event.preventDefault();

    const botonGuardar = formulario.querySelector('button[type="submit"]');
    // Lee los campos del formulario y los convierte en un objeto.
    const asociado = Object.fromEntries(new FormData(formulario).entries());

    botonGuardar.disabled = true;// Deshabilitar el botón mientras se procesa la solicitud

    try {
        const respuesta = await fetch(formulario.action, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(asociado) });

        // Pasar la respuesta a JSON a un objeto de JavaScript
        const resultado = await respuesta.json();
        document.getElementById('mensajeRegistro').textContent = "Guardando...";

        document.getElementById('mensajeRegistro').dataset.tipo = '';

        document.getElementById('mensajeRegistro').textContent = resultado.mensaje;

        if (respuesta.ok && resultado.success) {
            formulario.reset();  //  Limpiar el formulario si la solicitud fue exitosa
        }
    } catch (error) {
        console.error('Error al enviar el formulario:', error);
        document.getElementById("mensajeRegistro").textContent =
            "Ocurrió un error al enviar el formulario. Por favor, inténtelo de nuevo más tarde.";
    } finally {
        botonGuardar.disabled = false; // Habilitar el botón después de procesar la solicitud.
    }
});