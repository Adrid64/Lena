# 🏞️ Web Project: Lena Tourist Routes

**Interactive visualization of tourist routes for the Lena municipality (Asturias, Spain)**. Developed with modern web technologies, focused on accessibility and strict standards compliance.

---

## 📋 Table of Contents

- [🎯 Project Objective](#-project-objective)
- [🧩 Technologies Used](#-technologies-used)
- [🗺️ Main Features](#-main-features)
- [🧑‍💻 Project Structure](#-project-structure)
- [⚙️ Installation Instructions](#️-installation-instructions)
- [🗄️ Database](#️-database)
- [🧪 Usability Testing](#-usability-testing)
- [☁️ Cloud Deployment](#️-cloud-deployment)
- [👨‍🎓 Author](#-author)

---

## 🎯 Project Objective

Build an accessible, semantic and fully functional website for displaying and managing tourist routes in **Lena (Asturias)**. This project was created for the subject Web Software and Standards (SEW). It includes:

- Dynamic loading of routes from XML files.
- Graphical representation of altimetries (SVG) and planimetries (KML) on Google Maps.
- Management of reservations and tourist resources with object-oriented PHP backend and MySQL/MariaDB.
- Strict standards compliance: HTML5, CSS3 (Flexbox/Grid), WCAG AAA, no external libraries.

---

## 🧩 Technologies Used

| Technology        | Project Usage                                               |
|-------------------|------------------------------------------------------------|
| Semantic HTML5    | Accessible structure and W3C validation                    |
| CSS3 (Flex/Grid)  | Responsive layout, no floats or absolute positioning       |
| ECMAScript 6 (OO) | Interactivity and DOM management                           |
| PHP (OOP)         | Backend and database access                                |
| MySQL / MariaDB   | Normalized relational database                             |
| XML, SVG, KML     | Route, altimetry, and map persistence                      |
| Google Maps API   | Dynamic route visualization                                |

---

## 🗺️ Main Features

- **Dynamic loading of routes from XML**
- **Map rendering** with polylines and KML layers
- **Altimetry visualization** in SVG and photo gallery (max. 5)
- **Reservation form** with calculation and validation in PHP
- **Listing and cancellation of reservations**
- **Responsive and accessible design** (WCAG 2.0 AAA)

---

## 🧑‍💻 Project Structure

```
rutas-lena/
├── css/
│   ├── estilo.css         # General styles
│   └── layout.css         # Flex/Grid and media queries
├── js/
│   └── rutas.js           # OO logic for XML reading and maps
├── php/
│   ├── Database.php       # OOP MySQL connection
│   ├── Resource.php       # Tourist resource model
│   ├── Reservation.php    # Reservation model
│   ├── init.sql           # CREATE TABLE and FK statements
│   └── datos.csv          # Table initialization CSV
├── multimedia/
│   └── imagenes/          # Landmark photos
├── xml/
│   ├── rutas.xml          # Route data
│   ├── altimetria_.svg    # Route altimetries
│   └── ruta.kml           # KML planimetry
├── index.html
├── rutas.html
├── reservas.php
├── ayuda.html
└── documentacion/
    └── informe_proyecto.pdf # Cover, ER diagram, usability, deployment
```

---

## ⚙️ Installation Instructions

1. **Clone the repository:**  
   ```bash
   git clone https://github.com/yourusername/rutas-lena.git
   cd rutas-lena
   ```

2. **Set up your PHP + MySQL server** (XAMPP, Laragon, etc.)

3. **Import the database:**
   - In phpMyAdmin, create a new database.
   - Run `php/init.sql` to create tables and relationships.
   - (Optional) Import `php/datos.csv` for sample data.

4. **Edit credentials in `php/Database.php`:**
   ```php
   $usuario  = "DBUSER2025";
   $password = "DBPWD2025";
   $bd       = "database_name";
   ```

5. **Access the app:**  
   Open `rutas.html` in your browser and test loading `xml/rutas.xml`.

> **Note:** In `documentacion/informe_proyecto.pdf` you will find a more detailed installation guide, with screenshots and step-by-step examples.

---

## 🗄️ Database

- 5 normalized tables related via foreign keys (FK).
- Entity–Relationship diagram included in the documentation.
- Tables:
  - `usuarios`
  - `tipo_recursos`
  - `recursos_turisticos`
  - `puntos_interes`
  - `reservas`

---

## 🧪 Usability Testing

- **Devices:** Desktop, tablet, mobile.
- **Participants:** 12 users of different ages.
- **Tasks:** View routes, calculate price, create/cancel reservations.
- **Results:** Detailed report in `documentacion/informe_proyecto.pdf`.

---

## ☁️ Cloud Deployment

- **Server:** Ubuntu 20.04 + Apache + MySQL
- **Process:** Remote setup, data import, and final testing
- **URL:** (omitted for academic privacy)

---

## 👨‍🎓 Author

[Adrian Dumitru]  
University of Oviedo — Bachelor’s Degree in Software Engineering  
UO: UO295652 | Academic year: 2024/2025 | Subject: Web Software and Standards

This project fulfills all academic requirements: PHP OOP, CSS Grid/Flexbox, AAA accessibility, and exclusive use of native technologies.
