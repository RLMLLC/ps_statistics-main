# Generador de Informes de Ventas para PrestaShop v1.01

Un script PHP independiente y protegido por contraseña para generar informes de ventas avanzados directamente desde una base de datos de PrestaShop. Esta herramienta está diseñada como una solución rápida y potente para administradores que necesitan acceso a datos de ventas complejos sin depender de módulos o software externo.

!

## 🚀 Características Principales

* **Acceso Seguro**: Protegido por una contraseña única y reforzado con un archivo `.htaccess` para bloquear el acceso a archivos sensibles.
* **Interfaz Mejorada**:
    * Un único formulario para seleccionar el tipo de informe, rango de fechas y formato.
    * **Atajos de Fechas Rápidos**: Botones para rellenar automáticamente rangos comunes (Hoy, Ayer, Mes actual, Año anterior, etc.).
    * **Función de Logout**: Un enlace para cerrar la sesión de forma segura.
* **Informes Detallados y Flexibles**:
    * **Informe de Ventas General**: Basado en fechas de **factura**, incluye nº de factura, DNI/IVA del cliente y una **fila final con los totales** de importes y beneficios.
    * **Informe de Ventas por Cliente**: Genera un listado principal con totales por cliente y un **informe separado con el Top 10** de los clientes más valiosos.
    * **Informe de Ventas por Marca**: Genera un listado detallado por producto y un **informe separado con un resumen de totales** (coste, venta, beneficio) para cada marca.
* **Exportaciones Avanzadas (XLSX y CSV)**:
    * **Excel (XLSX)**: Los informes complejos se generan en un único archivo `.xlsx` con **múltiples hojas** convenientemente nombradas (ej. "Informe" y "Top 10").
    * **CSV**: Para informes con múltiples tablas, el script genera un único archivo **`.zip`** que contiene los ficheros `.csv` separados para una fácil gestión.
* **Instalación Súper Sencilla**: No requiere Composer ni acceso a la línea de comandos para el usuario final. Simplemente se sube a un directorio en el servidor y se configuran las credenciales en un único archivo.

---

## 📋 Requisitos

* Servidor web con **PHP 7.4** o superior (con la extensión `zip` habilitada).
* Acceso de lectura a la base de datos de PrestaShop.

---

## 🛠️ Instalación

### Método 1: Descarga Directa (Recomendado)

1.  **Descarga el Proyecto:**
    * Descarga la última versión directamente desde este enlace: **[Descargar ZIP](https://github.com/RLMLLC/ps_statistics-main/archive/refs/heads/main.zip)**.

2.  **Sube los Archivos a tu Servidor:**
    * Descomprime el archivo `.zip` en tu ordenador.
    * Usando un cliente FTP (como FileZilla) o el Administrador de Archivos de tu hosting, sube la carpeta completa del proyecto a un directorio en tu servidor web (por ejemplo, dentro de `public_html/informes/`).

3.  **Configura el script `index.php`:**
    * Abre el archivo `index.php` y modifica las constantes de configuración en la parte superior con los datos de tu base de datos de PrestaShop y una contraseña segura.

4.  **(Recomendado) Revisa el archivo `.htaccess`:**
    * Para una seguridad extra, puedes abrir el archivo `.htaccess` y configurar la restricción por IP descomentando las líneas correspondientes y añadiendo tu dirección IP.

### Método 2: Git Clone (Para desarrolladores)

```bash
# Clona el repositorio en tu máquina local
git clone [https://github.com/RLMLLC/ps_statistics-main.git](https://github.com/RLMLLC/ps_statistics-main.git)

# Sube la carpeta a tu servidor y sigue los pasos 3 y 4 de la instalación normal.
```

---

## 💻 Uso

1.  **Accede al script** a través de tu navegador (ej: `https://www.tutienda.com/informes/`).
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

## ✍️ Autor y Licencia

Este proyecto ha sido desarrollado por **Red, Light & Magic Consulting, LLC** ([rlm.llc](https://rlm.llc)).

El código está disponible bajo la **Licencia MIT**. Consulta el archivo `LICENSE` para más detalles.
