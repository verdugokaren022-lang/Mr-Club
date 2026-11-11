document.addEventListener('DOMContentLoaded', () =>{
const logueado = localStorage.getItem('logueadoo');

if (logueado !== 'true') {
    window.location.href = 'login.html';
    return;
}


const nombre = localStorage.getItem('nombre');
const rol = localStorage.getItem('rol');

document.getElementById('nombreUsuario').textContent = nombre;

if (rol === 'admin') {
    document.getElementById('tarjetasAdministrador').classList.remove('d-none');
    document.getElementById('tarjetasAsociado').classList.add('d-none');
}else{
    document.getElementById('tarjetasAdministrador').classList.add('d-none');
    document.getElementById('tarjetasAsociado').classList.remove('d-none');
}
});


async function cerrarSesion() {
    try{
    const respuesta=await fetch("../Modelo/cerrarSesion.php", {method: 'POST'});
     
    if (!respuesta.ok) {
        throw new Error("Error al cerrar sesión");
    }
        localStorage.clear();
        window.location.href = 'login.html';

    }catch (error) {
        console.error("Error al cerrar sesión:", error);
    }
}