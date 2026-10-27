const usuarios = [
    { usuario: 'admin@gmail.com', clave: '123', rol: 'admin', nombre: 'Admin MR' },
    { usuario: 'pepito@gmail.com', clave: '123', rol: 'asociado', nombre: 'Juan Perez' }
];

document.getElementById('FormuLogin').addEventListener('submit', function (e) {
    e.preventDefault();

    const usuarioInput = document.getElementById('Email').value;
    const claveInput = document.getElementById('Password').value;
    
    const usuarioEncontrado = usuarios.find(
        u => u.usuario === usuarioInput && u.clave === claveInput
    );
    if(usuarioEncontrado) {
        localStorage.setItem('logueadoo', 'true');
        localStorage.setItem('rol', usuarioEncontrado.rol);
        localStorage.setItem('nombre', usuarioEncontrado.nombre);
        window.location.href = 'dashboard.html';
    }else{
        alert('Usuario o contraseña incorrectos');
    }
}); 