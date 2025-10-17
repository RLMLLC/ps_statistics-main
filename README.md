# Generador de Informes de Ventas para PrestaShop

Un script PHP independiente y protegido por contraseña para generar informes de ventas avanzados directamente desde una base de datos de PrestaShop. Esta herramienta está diseñada como una solución rápida y potente para administradores que necesitan acceso a datos de ventas complejos sin depender de módulos o software externo.

!

## 🚀 Características Principales

* **Acceso Seguro**: Protegido por una contraseña única y reforzado con un archivo `.htaccess` para bloquear el acceso a archivos sensibles.
* **Interfaz Mejorada**:
    * Un único formulario para seleccionar el tipo de informe, rango de fechas y formato.
    * **Atajos de Fechas Rápidos**: Botones para rellenar automáticamente rangos comunes (Hoy, Ayer, Mes actual, Año anterior, etc.).
    * **Función de Logout**: Un botón para cerrar la sesión de forma segura.
* **Informes Detallados y Flexibles**:
    * **Informe de Ventas General**: Basado en fechas de **factura**, incluye nº de factura, DNI/IVA del cliente y una **fila final con los totales** de importes y beneficios.
    * **Informe de Ventas por Cliente**: Genera un listado principal con totales por cliente y un **informe separado con el Top 10** de los clientes más valiosos.
    * **Informe de Ventas por Marca**: Genera un listado detallado por producto y un **informe separado con un resumen de totales** (coste, venta, beneficio) para cada marca.
* **Exportaciones Avanzadas (XLSX y CSV)**:
    * **Excel (XLSX)**: Los informes complejos se generan en un único archivo `.xlsx` con **múltiples hojas** convenientemente nombradas (ej. "Informe" y "Top 10").
    * **CSV**: Para informes con múltiples tablas, el script genera un único archivo **`.zip`** que contiene los ficheros `.csv` separados para una fácil gestión.
* **Independiente y Fácil de Configurar**: No requiere instalación en PrestaShop. Simplemente se sube a un directorio en el servidor y se configuran las credenciales en un único archivo.

---

## 📋 Requisitos

* Servidor web con **PHP 7.4** o superior (con la extensión `zip` habilitada).
* **Composer** para gestionar las dependencias.
* Acceso de lectura a la base de datos de PrestaShop.

---

## 🛠️ Instalación y Configuración

1.  **Clona o descarga el repositorio:**
    ```bash
    git clone [https://github.com/RLMLLC/ps_statistics-main.git]
    ```
    O descarga el ZIP y descomprímelo.

2.  **Instala las dependencias:**
    Navega a la carpeta del proyecto en tu terminal y ejecuta Composer.
    ```bash
    cd /ruta/a/la/carpeta/del/proyecto
    composer install
    ```
    Esto creará una carpeta `vendor/` con la librería `PhpSpreadsheet`.

3.  **Configura el script `index.php`:**
    Abre el archivo `index.php` y modifica las constantes de configuración en la parte superior:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'prestashop_db');
    define('DB_USER', 'usuario_db');
    define('DB_PASS', 'contraseña_db');
    define('DB_PREFIX', 'ps_');
    define('SCRIPT_PASSWORD', 'TuContraseñaSecreta');
    define('SHOP_NAME', 'Nombre de tu Tienda');
    ```

4.  **Sube los archivos a tu servidor:**
    Sube la carpeta completa del proyecto (`index.php`, `composer.json`, `composer.lock`, `.htaccess` y la carpeta `vendor/`) a un directorio en tu servidor web.

5.  **(Recomendado) Configura el archivo `.htaccess`:**
    Abre el archivo `.htaccess` incluido y, si lo deseas, descomenta y configura la sección de restricción por IP para añadir una capa extra de seguridad.

---

## 💻 Uso

1.  **Accede al script** a través de tu navegador (ej: `https://www.tutienda.com/reportes/`).
2.  **Introduce la contraseña** que definiste en la configuración.
3.  **Rellena el formulario**:
    * Selecciona el **tipo de informe**.
    * Usa los **botones de atajo** o selecciona manualmente un rango de fechas.
    * Selecciona el **formato de salida** (XLSX o CSV).
4.  Haz clic en **"Generar Informe"**. La descarga del archivo `.xlsx` o `.zip` comenzará automáticamente.
5.  Usa el enlace **"Cerrar Sesión"** cuando hayas terminado.

---

## 🤝 Contribuciones

Las contribuciones para mejorar esta herramienta son bienvenidas. Por favor, abre un *issue* para discutir cambios o envía un *pull request*.

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo `LICENSE` para más detalles.
