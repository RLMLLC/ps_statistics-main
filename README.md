[Español](#español) | [English](#english)

<a name="español"></a>
## 🇪🇸 Español

# Generador de Informes de Ventas e Inventario para PrestaShop v1.03b

Un script PHP independiente y protegido por contraseña para generar informes avanzados de ventas e inventario directamente desde una base de datos de PrestaShop. Esta herramienta está diseñada como una solución rápida y potente para administradores que necesitan acceso a datos complejos sin depender de módulos o software externo.

## 🚀 Características Principales

* **Acceso Seguro**: Protegido por una contraseña única y reforzado con un archivo `.htaccess` para bloquear el acceso a archivos sensibles.
* **Interfaz Mejorada**:
    * Un único formulario dinámico para seleccionar el tipo de informe y los parámetros necesarios (fechas, proveedor o umbral de stock).
    * **Atajos de Fechas Rápidos**: Botones para rellenar automáticamente rangos comunes (Hoy, Ayer, Mes actual, Año anterior, etc.) para los informes de ventas.
    * **Función de Logout**: Un enlace para cerrar la sesión de forma segura.
* **Informes Detallados y Flexibles**:
    * **Informe de Ventas General**: Basado en fechas de **factura**, incluye nº de factura, DNI/IVA del cliente y una **fila final con los totales** de importes y beneficios.
    * **Informe de Ventas por Cliente**: Genera un listado principal con totales por cliente y un **informe separado con el Top 10** de los clientes más valiosos.
    * **Informe de Ventas por Marca**: Genera un listado detallado por producto y un **informe separado con un resumen de totales** (coste, venta, beneficio) para cada marca.
    * **Informe de Productos por Proveedor**: Muestra un listado de todos los productos asociados a un proveedor específico, con su referencia, precio y stock actual.
    * **Informe de Existencias Bajas**: Lista todos los productos (y sus combinaciones) cuyo stock actual sea igual o inferior a un umbral definido por el usuario, mostrando referencia, precio, fabricante y proveedor.
* **Exportaciones Avanzadas (XLSX y CSV)**:
    * **Excel (XLSX)**: Los informes complejos se generan en un único archivo `.xlsx` con **múltiples hojas** convenientemente nombradas (ej. "Informe" y "Top 10").
    * **CSV**: Para informes con varias tablas (o para mantener la consistencia), el script genera un único archivo **`.zip`** que contiene los ficheros `.csv` separados.
* **Instalación Súper Sencilla**: No requiere Composer ni acceso a la línea de comandos para el usuario final. Simplemente se sube a un directorio en el servidor y se configuran las credenciales en un único archivo.

---

## 📋 Requisitos

* Servidor web con **PHP 7.2** o superior (con la extensión `zip` habilitada).
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
    * Selecciona el **tipo de informe**. Los campos siguientes se adaptarán automáticamente.
    * Para informes de ventas, usa los **botones de atajo** o selecciona manualmente un rango de fechas.
    * Para informe de proveedor, selecciona el **proveedor** deseado.
    * Para informe de existencias bajas, introduce el **umbral de stock**.
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

---

<a name="english"></a>
## 🇬🇧 English

# PrestaShop Sales & Inventory Report Generator v1.03b

A standalone, password-protected PHP script to generate advanced sales and inventory reports directly from a PrestaShop database. This tool is designed as a fast and powerful solution for administrators who need access to complex data without relying on external modules or software.

## 🚀 Key Features

* **Secure Access**: Protected by a unique password and enhanced with an `.htaccess` file to block access to sensitive files.
* **Enhanced Interface**:
    * A single dynamic form to select the report type and necessary parameters (dates, supplier, or stock threshold).
    * **Quick Date Shortcuts**: Buttons to automatically fill in common date ranges (Today, Yesterday, Current month, Last year, etc.) for sales reports.
    * **Logout Function**: A link to securely end the session.
* **Detailed & Flexible Reports**:
    * **General Sales Report**: Based on **invoice** dates, includes invoice number, customer VAT ID, and a **final row with totals** for amounts and profits.
    * **Sales by Customer Report**: Generates a main list with totals per customer and a **separate report with the Top 10** most valuable customers.
    * **Sales by Brand Report**: Generates a detailed list by product and a **separate report with a summary of totals** (cost, sale price, profit) for each brand.
    * **Products by Supplier Report**: Displays a list of all products associated with a specific supplier, including reference, price, and current stock.
    * **Low Stock Report**: Lists all products (and their combinations) whose current stock is equal to or below a user-defined threshold, showing reference, price, manufacturer, and supplier.
* **Advanced Exports (XLSX & CSV)**:
    * **Excel (XLSX)**: Complex reports are generated in a single `.xlsx` file with **multiple, conveniently named sheets** (e.g., "Report" and "Top 10").
    * **CSV**: For reports with multiple tables (or for consistency), the script generates a single **`.zip`** file containing the separate `.csv` files.
* **Super Simple Installation**: No Composer or command-line access required for the end-user. Simply upload to a directory on the server and configure the credentials in a single file.

---

## 📋 Requirements

* Web server with **PHP 7.2** or higher (with the `zip` extension enabled).
* Read access to the PrestaShop database.

---

## 🛠️ Installation

### Method 1: Direct Download (Recommended)

1.  **Download the Project:**
    * Download the latest version directly from this link: **[Download ZIP](https://github.com/RLMLLC/ps_statistics-main/archive/refs/heads/main.zip)**.
2.  **Upload the Files to Your Server:**
    * Unzip the `.zip` file on your computer.
    * Using an FTP client (like FileZilla) or your hosting's File Manager, upload the entire project folder to a directory on your web server (e.g., inside `public_html/reports/`).
3.  **Configure the `index.php` script:**
    * Open the `index.php` file and modify the configuration constants at the top with your PrestaShop database details and a secure password.
4.  **(Recommended) Review the `.htaccess` file:**
    * For extra security, you can open the `.htaccess` file and configure IP restriction by uncommenting the corresponding lines and adding your IP address.

### Method 2: Git Clone (For Developers)

```bash
# Clone the repository to your local machine
git clone [https://github.com/RLMLLC/ps_statistics-main.git](https://github.com/RLMLLC/ps_statistics-main.git)

# Upload the folder to your server and follow steps 3 and 4 of the normal installation.
```

---

## 💻 Usage

1.  **Access the script** through your browser (e.g., `https://www.yourstore.com/reports/`).
2.  **Enter the password** you defined in the configuration.
3.  **Fill out the form**:
    * Select the **report type**. Subsequent fields will adapt automatically.
    * For sales reports, use the **shortcut buttons** or manually select a date range.
    * For the supplier report, select the desired **supplier**.
    * For the low stock report, enter the **stock threshold**.
    * Select the **output format** (XLSX or CSV).
4.  Click **"Generate Report"**. The download of the `.xlsx` or `.zip` file will start automatically.
5.  Use the **"Log Out"** link when you are finished.

---

## 🤝 Contributions

Contributions to improve this tool are welcome. Please open an issue to discuss changes or submit a pull request.

---

## ✍️ Author and License

This project was developed by **Red, Light & Magic Consulting, LLC** ([rlm.llc](https://rlm.llc)).

The code is available under the **MIT License**. See the `LICENSE` file for more details.
