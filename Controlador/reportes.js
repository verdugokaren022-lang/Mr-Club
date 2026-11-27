document.addEventListener("DOMContentLoaded", () => {
    const btnGenerarReporte = document.getElementById("btnGenerarReporte");
    const filtroReporte = document.getElementById("filtroReporte");

    if (btnGenerarReporte) {
        btnGenerarReporte.addEventListener("click", () => {
            alert("Generando reporte de asociados y saldos... La descarga comenzará en breve.");
        });
    }

    if (filtroReporte) {
        filtroReporte.addEventListener("change", (e) => {
            const opcionSeleccionada = e.target.value;
            console.log(`Filtrando reportes por: ${opcionSeleccionada}`);
        });
    }

    console.log("Módulo de Reportes inicializado.");
});