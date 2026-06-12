# 🛒 Sistema de Inventarios en PHP y JSON 💹

¡Bienvenido al **Sistema de Inventarios Web**! Esta es una aplicación web ágil, ligera y profesional desarrollada en PHP nativo que permite gestionar el stock de productos en tiempo real, almacenando toda la información de forma local en un archivo estructurado JSON (sin necesidad de configurar complejas bases de datos SQL).

---

## 🚀 Características Principales

* **CRUD Completo:** Permite **Agregar**, **Visualizar**, **Actualizar** (precio y cantidad de forma masiva buscando por nombre) y **Eliminar** productos del inventario.
* **Persistencia en JSON:** Automatiza la creación y lectura del archivo `productos.json`. Si el archivo no existe, el sistema lo crea por ti con una estructura limpia.
* **Módulo de Reportes Avanzado:** Exportación de datos al instante con un solo clic en tres formatos esenciales:
    * 📄 **TXT:** Formato plano ideal para lectura rápida.
    * 📊 **CSV:** Compatible con Microsoft Excel y Google Sheets (incluye codificación BOM para conservar tildes y eñes).
    * ⚙️ **JSON:** Estructura anidada con metadatos para integraciones externas.
* **Diseño Limpio:** Interfaz responsiva y minimalista estilizada con CSS moderno utilizando flexbox y componentes de alertas dinámicas (`flash messages`).

---

## 🛠️ Tecnologías Utilizadas

* **Backend:** PHP 7.4+ / PHP 8.x (Nativo)
* **Frontend:** HTML5, CSS3 (Fuentes de Google Fonts)
* **Base de datos:** JSON (JavaScript Object Notation)

---