const formulario = document.getElementById("FormuLogin");
formulario.addEventListener("submit", async function (e) {
    e.preventDefault();
    alert("Formulario enviado"); // Muestra un alert cuando se envía el formulario

    const boton = document.querySelector('button[type="submit"]');
    const mensaje = document.getElementById("mensajeLogin");
    boton.disabled= true;
    try {
        const respuesta = await fetch(formulario.action, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(
                Object.fromEntries(new FormData(formulario).entries()),// Convertir los datos del formulario en un objeto y luego a JSON
        ),
    });

alert("Respuesta recibida"); // Muestra un alert cuando se recibe la respuesta del servidor
   
const resultado = await respuesta.json();// Pasar la respuesta a JSON a un objeto de JavaScript
    if(!resultado.success) {
        mensaje.textContent = 
        "No fue posible iniciar sesión. Por favor, verifique sus credenciales.";
    boton.disabled = false;
    return;// Salir de la función si la respuesta no es exitosa
    }
    
alert("Inicio de sesión exitoso"); // Muestra un alert cuando el inicio de sesión es exitoso

    localStorage.setItem("logueado", "true");
    localStorage.setItem("rol", resultado.rol);
    localStorage.setItem("nombre", resultado.nombre);

    window.location.href = "dashboard.html";// Redirigir al usuario a la página de dashboard si la respuesta es exitosa


    }catch (error) {
        console.error("Error al enviar el formulario: ", error);
        mensaje.textContent = 
            "Ocurrió un error al enviar el formulario. Por favor, inténtelo de nuevo más tarde.";
    } finally {
        boton.disabled = false;
    }
});