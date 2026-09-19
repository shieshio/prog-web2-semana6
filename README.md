# Sistema de Gestión de Tienda Web (Semana 6)

## 📚 Descripción del Proyecto:

Este proyecto es un sistema de gestión de tienda en línea basado en PHP y MySQL. El objetivo principal es permitir a los usuarios realizar compras, gestionar sus carritos de compras, y proporcionar un seguimiento de los pedidos. Además, ofrece una sección de clientes para gestionar sus datos y realizar compras en línea. Aplicación web desarrollada en PHP y MySQL/MariaDB para la gestión de productos, clientes, transacciones de compra y control de sesiones seguras.

---

## 📚 Características Principales:

1. **Gestión de Productos:** Permite agregar, editar, eliminar y filtrar productos.
2. **Gestión de Clientes:** Permite registrar, editar, eliminar y filtrar clientes.
3. **Carrito de Compras:** Permite agregar, eliminar y filtrar productos en el carrito de compras.
4. **Control de Pedidos:** Permite registrar, editar, eliminar y filtrar pedidos.
5. **Control de Sesiones:** Permite iniciar, cerrar y filtrar sesiones de usuario.
6. **Claridad inmediata:** Cualquier persona que abra el repositorio sabrá de qué trata el proyecto sin leer explicaciones extensas.
7. **Especificaciones precisas:** Incluye el puerto `3307`, MariaDB y el uso de `phpserver` (`localhost:8000`), evitando confusiones con los puertos predeterminados (`3306` u otros).
8. **Pasos rápidos de ejecución:** 3 pasos sencillos para levantar la base de datos y el servidor web local.

---

## ⚙️ Entorno de Desarrollo y Requisitos

* **Servidor Web:** PHP 8.5.10 (Development Server)
* **Base de Datos:** MariaDB 12.3.3 (Arch Linux)
* **Parámetros de Conexión:**
  * **Host:** `localhost` / `127.0.0.1`
  * **Puerto:** `3307`
  * **Usuario:** `root`
  * **Base de Datos:** `TIENDA`

---

## 🚀 Despliegue Rápido

1. **Importar la Base de Datos:**
   Ejecutar el script SQL ubicado en `schemas/tienda.sql` en tu gestor de base de datos (puerto `3307`).

2. **Iniciar el Servidor PHP:**
   Desde la carpeta raíz del proyecto (`sem_6/`), iniciar el servidor de desarrollo:
   ```bash
   phpserver
   # O alternativamente: php -S localhost:8000

---

## 📁 Estructura de Carpeta del trabajo para la semana 6:
├── config
│   ├── catalogo.php
│   ├── conexion.php
│   ├── logout.php
│   ├── mantenimiento.php
│   └── sesion.php
├── formularios
│   ├── gestion_clientes.php
│   └── gestion_productos.php
├── funciones
│   ├── actualizar_carrito.php
│   ├── agregar_carrito.php
│   ├── comprar.php
│   ├── confirmar_recepcion.php
│   ├── enviar_resena.php
│   ├── guardar_pedido.php
│   ├── mostrador_clientes_multicompra.php
│   ├── procesar_cliente.php
│   ├── procesar_producto.php
│   ├── registrar_compra.php
│   ├── seguimiento.php
│   └── ver_estado.php
├── schemas
│   └── tienda.sql
├── index.php
├── login.php
├── README.md
├── scripts.js
└── styles.css
---
