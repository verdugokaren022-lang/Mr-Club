const formActividad = document.getElementById("formActuvidades");
let esAdministrador= false;
let actividades = [];
let accionesConfiguradas = false;
const formularioActividad=document.getElementById("formActividad");
const modalActividad=new bootstrap.Modal(
    document.getElementById("modalActividad")
);

async function solicitarApi(metodo, datos) {
    const opciones = {
        method: metodo,
        headers: { "Content-Type": "application/json" },
    };

    if (datos) {
        opciones.body = JSON.stringify(datos);
    }

    const respuesta = await fetch("../Modelo/gestionarActividades.php", opciones);
    const resultado = await respuesta.json();

    if (respuesta.status === 401) {
        window.location.href = "login.html";
    }

    if (!respuesta.ok || !resultado.success) {
        throw new Error(resultado.message || "Error en la solicitud");
    }

    return resultado;
}

async function cargarActividades() {
    try {
        const resultado = await solicitarApi("GET");

        esAdministrador = resultado.rol === "admin";
        configurarAcciones();

        actividades = resultado.actividades;
        mostrarActividades();
        
    } catch (error) {
        alert("Error al cargar las actividades: " + error.message);
    }

    function mostrarActividades(){
        listaActividades.replaceChildren();

        if (actividades.length === 0){
            const fila = document.createElement("tr");
            const celda = document.createElement("td");
            celda.colSpan = esAdministrador ? 5 : 4;
            celda.className = "text-center py-4 text-muted";
            celda.textContent= "No hay actividades disponibles.";
            fila.append(celda);
            listaActividades.append(fila);
            return; 
    }


    actividades.forEach((actividad) => {
        const fila = document.createElement("tr");
        fila.append(
            crearCelda(actividad.nombreActividad),
            crearCelda(actividad.fechaEvento),
            crearCelda(actividad.municipio),
            crearCelda(actividad.totalRecaudo===null?"Pendiente":Number(actividad.totalRecaudo).toLocaleString("es-CO", { style: "currency", currency: "COP" })),
        );
        if(esAdministrador){
            const acciones = document.createElement("td");
            acciones.className="text-nowrap";
            
            const botonEditar = document.createElement("button");
            botonEditar.type = "button";
            botonEditar.className = "btn btn-sm btn-outline-primary me-2";
            botonEditar.innerHTML = '<i class="bi bi-pencil-square"></i>';
            botonEditar.addEventListener("click", () => abrirEdicion(actividad));

            const botonEliminar = document.createElement("button");
            botonEliminar.type = "button";
            botonEliminar.className = "btn btn-sm btn-outline-danger";
            botonEliminar.innerHTML = '<i class="bi bi-trash"></i>';
            botonEliminar.addEventListener("click", () => eliminarActividad(actividad));

            acciones.append(botonEditar, botonEliminar);
            fila.append(acciones);
        }
        listaActividades.append(fila);
        });
    }
}
    function crearCelda(texto) {
        const celda = document.createElement("td");
        celda.textContent = texto;
        return celda;
    }

    async function eliminarActividad(actividad) {
        window.confirm(`¿Deseas eliminar la actividad "${actividad.nombreActividad}"?`,);

        if(!confirmar){
            return
        }

        try {
            await solicitarApi("DELETE", {idActividad:actividad.idActividad});
            await cargarActividades();
            alert("actividad eliminada correctamente");
        } catch (error) {
            alert(error.message);
        }
    }

    function configurarAcciones(){
        if(accionesConfiguradas){
            return;
        }
        const botonNueva = document.getElementById("nuevaActividad");
        const encabezadoAcciones = document.getElementById("encabezadoAcciones");

        if(esAdministrador) {
            botonNueva.classList.remove("d-none");
            encabezadoAcciones.classList.remove("d-nome");
            accionesConfiguradas = true;
            return;
        }
    }

    function abrirEdicion(actividad) {
        formularioActividad.reset();
        document.getElementById("idActividad").value = actividad.idActividad;
        document.getElementById("nombreActividad").value = actividad.nombreActividad;
        document.getElementById("fechaEvento").value = actividad.fechaEvento;
        document.getElementById("municipio").value = actividad.municipio;
        document.getElementById("totalRecaudado").value = 
          actividad.totalRecaudado ?? "";

          document.getElementById("campoTotalRecaudado").classList.remove("d-none");
          document.getElementById("tituloModalActividad").textContent =
           "Editar actividad";
           document.getElementById("guardarActividad").textContent = "Guardar cambios";
           modalActividad.show();
    }
    formularioActividad.addEventListener("submit", async (evento) => {
        evento.preventDefault();
        const idActividad=document.getElementById("idActividad").value;

        const datos = {
            nombreActividad: document.getElementById("nombreActividad").value.trim(),
            fechaEvento: document.getElementById("fechaEvento").value,
            municipio: document.getElementById("municipio").value.trim(),
        };
        if(idActividad) {
            datos.idActividad = Number(idActividad);
            const total = document.getElementById("totalRecaudado").value;
            datos.totalRecaudado = total === "" ? null : Number(total);// si esta vacio envia que es nulo es un condicional.
        }

        try{
            await solicitarApi(idActividad ? "PUT" :"POST",datos);
            modalActividad.hide();
            alert("actualizada correctamente");
            cargarActividades();
        } catch (error) {
            alert(error.mensaje);
        }
    });

    cargarActividades();