# 🏞️ Proyecto Web: Rutas Turísticas de Lena

**Visualización interactiva de rutas turísticas para el concejo de Lena (Asturias)**. Desarrollado con tecnologías web modernas, orientado a la accesibilidad y al cumplimiento estricto de estándares.

---

## 📋 Índice

- [🎯 Objetivo del proyecto](#-objetivo-del-proyecto)
- [🧩 Tecnologías utilizadas](#-tecnologías-utilizadas)
- [🗺️ Funcionalidades principales](#-funcionalidades-principales)
- [🧑‍💻 Estructura del proyecto](#-estructura-del-proyecto)
- [⚙️ Instrucciones de instalación](#️-instrucciones-de-instalación)
- [🗄️ Base de datos](#️-base-de-datos)
- [🧪 Pruebas de usabilidad](#-pruebas-de-usabilidad)
- [☁️ Despliegue en la nube](#️-despliegue-en-la-nube)
- [👨‍🎓 Autoría](#-autoría)

---

## 🎯 Objetivo del proyecto

Crear un sitio web accesible, semántico y totalmente funcional para visualizar y gestionar rutas turísticas en el concejo de **Lena (Asturias)**. Este proyecto fue realizado para la asignatura de Software y Estandares para la web(SEW). Incluye:

- Carga dinámica de rutas desde archivos XML.
- Representación gráfica de altimetrías (SVG) y planimetrías (KML) en Google Maps.
- Gestión de reservas y recursos turísticos con backend PHP orientado a objetos y MySQL/MariaDB.
- Cumplimiento estricto de los estándares: HTML5, CSS3 (Flexbox/Grid), WCAG AAA, sin bibliotecas externas.

---

## 🧩 Tecnologías utilizadas

| Tecnología        | Uso en el proyecto                                         |
|-------------------|------------------------------------------------------------|
| HTML5 semántico   | Estructura accesible y validación W3C                      |
| CSS3 (Flex/Grid)  | Maquetación responsive sin float ni posicionamiento absoluto|
| ECMAScript 6 (OO) | Interactividad y gestión del DOM                           |
| PHP (POO)         | Backend y acceso a base de datos                           |
| MySQL / MariaDB   | Base de datos relacional normalizada                       |
| XML, SVG, KML     | Persistencia de rutas, altimetrías y mapas                 |
| Google Maps API   | Visualización dinámica de rutas                            |

---

## 🗺️ Funcionalidades principales

- **Carga dinámica de rutas desde XML**
- **Renderizado de mapas** con polilíneas y capas KML
- **Visualización de altimetrías** en SVG y galería de fotos (máx. 5)
- **Formulario de reservas** con cálculo y validación en PHP
- **Listado y cancelación de reservas**
- **Diseño responsive** y accesible (WCAG 2.0 AAA)

---

## 🧑‍💻 Estructura del proyecto

```
rutas-lena/
├── css/
│   ├── estilo.css         # Estilos generales
│   └── layout.css         # Flex/Grid y media queries
├── js/
│   └── rutas.js           # Lógica OO de lectura XML y mapas
├── php/
│   ├── Database.php       # Conexión POO a MySQL
│   ├── Resource.php       # Modelo de recurso turístico
│   ├── Reservation.php    # Modelo de reserva
│   ├── init.sql           # Sentencias CREATE TABLE y FK
│   └── datos.csv          # CSV de inicialización de tablas
├── multimedia/
│   └── imagenes/          # Fotos de hitos
├── xml/
│   ├── rutas.xml          # Datos de rutas
│   ├── altimetria_.svg    # Altimetrías de rutas
│   └── ruta.kml           # Planimetría KML
├── index.html
├── rutas.html
├── reservas.php
├── ayuda.html
└── documentacion/
    └── informe_proyecto.pdf # Portada, ER, usabilidad, despliegue
```

---

## ⚙️ Instrucciones de instalación

1. **Clona el repositorio:**  
   ```bash
   git clone https://github.com/tuusuario/rutas-lena.git
   cd rutas-lena
   ```

2. **Configura tu servidor PHP + MySQL** (XAMPP, Laragon, etc.)

3. **Importa la base de datos:**
   - En phpMyAdmin, crea una base de datos nueva.
   - Ejecuta `php/init.sql` para crear las tablas y relaciones.
   - (Opcional) Importa `php/datos.csv` para datos de ejemplo.

4. **Ajusta credenciales en `php/Database.php`:**
   ```php
   $usuario  = "DBUSER2025";
   $password = "DBPWD2025";
   $bd       = "nombre_basedatos";
   ```

5. **Accede a la aplicación:**  
   Abre `rutas.html` en tu navegador y prueba cargando `xml/rutas.xml`.

> **Nota:** En el documento `documentacion/informe_proyecto.pdf` se incluye una explicación más detallada de las instrucciones de instalación, con capturas y ejemplos paso a paso.

---

## 🗄️ Base de datos

- 5 tablas normalizadas relacionadas mediante claves foráneas (FK).
- Diagrama Entidad–Relación incluido en la documentación.
- Tablas:
  - `usuarios`
  - `tipo_recursos`
  - `recursos_turisticos`
  - `puntos_interes`
  - `reservas`

---

## 🧪 Pruebas de usabilidad

- **Dispositivos:** Escritorio, tablet y móvil.
- **Participantes:** 12 usuarios de distintas edades.
- **Tareas:** Visualizar rutas, calcular precio, crear/cancelar reservas.
- **Resultados:** Informe detallado en `documentacion/informe_proyecto.pdf`.

---

## ☁️ Despliegue en la nube

- **Servidor:** Ubuntu 20.04 + Apache + MySQL
- **Proceso:** Puesta en marcha remota, importación de datos y pruebas finales
- **URL:** (se omite por privacidad académica)

---

## 👨‍🎓 Autoría

[Adrian Dumitru]  
Universidad de Oviedo — Grado en Ingeniería Informática del software 
UO: UO295652 | Curso: 2024/2025 | Asignatura: Software y estándares para la web

Este proyecto cumple todos los requisitos académicos: POO en PHP, CSS Grid/Flexbox, accesibilidad AAA y uso exclusivo de tecnologías nativas.