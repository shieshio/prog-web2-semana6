// ===========================================
// CONFIGURACIÓN
// ===========================================
const csrfToken = window.CSRF_TOKEN || "";

function formatearCLP(monto) {
  return "$" + monto.toLocaleString("es-CL");
}

// Inyecta el token CSRF en formularios dinámicos
function inputSecreto(form, token) {
  const input = document.createElement("input");
  input.type = "hidden";
  input.name = "csrf";
  input.value = token;
  form.appendChild(input);
}

// ===========================================
// RENDERIZADO CON DocumentFragment (OPTIMIZACIÓN DOM)
// ===========================================
// Se construye TODO en memoria y se inserta UNA SOLA VEZ al DOM
// Reduce reflojos desde N repeticiones (appendChild en ciclo) a 1 sola inserción

function mostrarProductos(lista) {
  const tablaBody = document.getElementById("tabla-body");
  tablaBody.innerHTML = "";

  const fragmento = document.createDocumentFragment();

  for (const producto of lista) {
    const fila = document.createElement("tr");

    const celdaId = document.createElement("td");
    celdaId.textContent = producto.id;
    fila.appendChild(celdaId);

    const celdaNombre = document.createElement("td");
    celdaNombre.textContent = producto.nombre;
    fila.appendChild(celdaNombre);

    const celdaCat = document.createElement("td");
    celdaCat.textContent = producto.categoria;
    fila.appendChild(celdaCat);

    const celdaPrecio = document.createElement("td");
    celdaPrecio.textContent = formatearCLP(producto.precio);
    fila.appendChild(celdaPrecio);

    const celdaAccion = document.createElement("td");
    const form = document.createElement("form");
    form.method = "post";
    form.action = "funciones/agregar_carrito.php";

    inputSecreto(form, csrfToken);

    const inputId = document.createElement("input");
    inputId.type = "hidden";
    inputId.name = "id";
    inputId.value = producto.id;
    form.appendChild(inputId);

    const boton = document.createElement("button");
    boton.type = "submit";
    boton.textContent = "Agregar";
    form.appendChild(boton);

    celdaAccion.appendChild(form);
    fila.appendChild(celdaAccion);

    fragmento.appendChild(fila); // Acumulamos en memoria
  }

  tablaBody.appendChild(fragmento); // ← UNA SOLA manipulación del DOM
}

// ===========================================
// SELECTORES DEL DOM
// ===========================================
const filtroCategoria = document.getElementById("filtro-categoria");
const filtroPrecio = document.getElementById("filtro-precio");
const btnLimpiar = document.getElementById("btn-limpiar");
const aviso = document.getElementById("aviso");

// ===========================================
// FILTROS EN TIEMPO REAL (input/change) - SIN alert()
// ===========================================
// Eventos 'input' (escritura en vivo) y 'change' (cambio de select)
// Actualizan resultados dinámicamente mientras el usuario interactúa

function filtrarProductos() {
  const categoria = filtroCategoria.value;
  const precioMax = parseInt(filtroPrecio.value, 10);

  const filtrados = window.PRODUCTOS.filter(function (prod) {
    const matchCat = categoria === "" || prod.categoria === categoria;
    const matchPrecio = isNaN(precioMax) || prod.precio <= precioMax;
    return matchCat && matchPrecio;
  });

  mostrarProductos(filtrados);

  // reemplaza alert()
  if (aviso) {
    aviso.hidden = false;
    aviso.textContent =
      "Mostrando " +
      filtrados.length +
      " de " +
      window.PRODUCTOS.length +
      " productos";
  }
}

// 'change' para el select, 'input' para escritura en vivo
filtroCategoria.addEventListener("change", filtrarProductos);
filtroPrecio.addEventListener("input", filtrarProductos);

btnLimpiar.addEventListener("click", function () {
  filtroCategoria.value = "";
  filtroPrecio.value = "";
  aviso.hidden = true;
  mostrarProductos(window.PRODUCTOS);
});

// Render inicial
mostrarProductos(window.PRODUCTOS);
