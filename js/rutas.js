/* js/rutas.js – versión sin id, sin class, sin div, sin etiquetas de estilo */
"use strict";

class RutasCompletas {
  constructor (apiKey) {
    this.apiKey      = apiKey;
    this.$fileInput  = $("main > section > section input[type=file]");
    this.$contenedor = $("main > section > section + section");   /* contenedor general */

    this.$fileInput.on("change", e => this._leerXML(e.target.files[0]));
  }

  /* --------------------------------------------------------- */
  /* 1 · Carga y parseo del XML                                */
  /* --------------------------------------------------------- */
  _leerXML (file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = evt => this._procesarXML(evt.target.result);
    reader.readAsText(file);
  }

  _procesarXML (xmlText) {
    const xml = new DOMParser().parseFromString(xmlText, "application/xml");
    if (xml.querySelector("parsererror")) {
      console.error("Error al parsear el XML.");
      return;
    }
    this.$contenedor.empty();                       /* limpieza */
    Array.from(xml.querySelectorAll("ruta"))
         .forEach((nodo, i) => this._procesarRuta(nodo, i + 1));
  }

  /* --------------------------------------------------------- */
  /* 2 · Procesamiento de cada <ruta>                          */
  /* --------------------------------------------------------- */
  _procesarRuta (nodo, idx) {
    const txt = sel => nodo.querySelector(sel)?.textContent.trim() || "";

    /* ── Datos base ────────────────────────────────────────── */
    const nombre      = txt("nombre")      || `Ruta ${idx}`;
    const tipo        = txt("tipo");
    const transporte  = txt("transporte");
    const duracion    = txt("duracion");
    const agencia     = txt("agencia");
    const descripcion = txt("descripcion");
    const personas    = txt("personas");
    const recomend    = txt("recomendacion");

    /* ── Bloque INICIO ─────────────────────────────────────── */
    const ini = nodo.querySelector("inicio");
    let inicioHTML = "";
    if (ini) {
      inicioHTML = `
        <h5>Inicio</h5>
        <ul>
          <li>Lugar: ${txt("inicio > lugar")}</li>
          <li>Dirección: ${txt("inicio > direccion")}</li>
          <li>Coordenadas: (
                ${txt("inicio > coordenadas > latitud")},
                ${txt("inicio > coordenadas > longitud")}
              ), alt ${txt("inicio > coordenadas > altitud")} m
          </li>
        </ul>`;
    }

    /* ── Bloque REFERENCIAS ────────────────────────────────── */
    const refs = Array.from(nodo.querySelectorAll("referencias > referencia"))
                      .map(r => r.textContent.trim());
    const refsHTML = refs.length
      ? `<h5>Referencias</h5>
         <ul>${refs.map(u => {
              const visible = u.length > 40 ? u.slice(0, 25) + "…" : u;
              return `<li><a href="${u}" target="_blank" rel="noopener">${visible}</a></li>`;
           }).join("")}</ul>`
      : "";

    /* ── Bloque HITOS ──────────────────────────────────────── */
    const hitos = Array.from(nodo.querySelectorAll("hitos > hito")).map(h => {
      const n   = h.querySelector("nombre")?.textContent.trim()        || "";
      const d   = h.querySelector("descripcion")?.textContent.trim()   || "";
      const km  = h.querySelector("distancia")?.textContent.trim()     || "";
      const lat = h.querySelector("coordenadas > latitud")?.textContent.trim()  || "";
      const lng = h.querySelector("coordenadas > longitud")?.textContent.trim() || "";
      const alt = h.querySelector("coordenadas > altitud")?.textContent.trim()  || "";

      /* Galería de fotos (máx. 5) */
      const fotos = Array.from(h.querySelectorAll("galeriaFotos > fotografia"))
                         .map(f => f.textContent.trim());
      let galeriaHTML = "";
      if (fotos.length) {
        galeriaHTML = "<figure>" +
          fotos.slice(0, 5).map(f => {
            const src = /^https?:\/\//i.test(f) ? f : `multimedia/imagenes/${f}`;
            return `<img src="${src}" alt="${n}" loading="lazy">`;
          }).join("") +
        "</figure>";
      }

      return `<li>
                ${n} (${km} km)<br>
                ${d}<br>
                Coords: (${lat}, ${lng}), alt ${alt} m
                ${galeriaHTML}
              </li>`;
    });

    const hitosHTML = hitos.length ? `<h5>Hitos</h5><ul>${hitos.join("")}</ul>` : "";

    /* ── Coordenadas para polilínea / centro de mapa ───────── */
    const coords = [];
    if (ini) coords.push({
      lat: parseFloat(ini.querySelector("coordenadas > latitud")?.textContent),
      lng: parseFloat(ini.querySelector("coordenadas > longitud")?.textContent)
    });
    nodo.querySelectorAll("hitos > hito").forEach(h => {
      coords.push({
        lat: parseFloat(h.querySelector("coordenadas > latitud")?.textContent),
        lng: parseFloat(h.querySelector("coordenadas > longitud")?.textContent)
      });
    });

    /* ── Construcción de la sección ruta ───────────────────── */
    const sec = document.createElement("section");

    sec.append(
      Object.assign(document.createElement("h4"), { textContent: nombre }),
      Object.assign(document.createElement("p"),  { textContent: descripcion })
    );

    const dl = document.createElement("dl");
    [
      ["Tipo",         tipo],
      ["Transporte",   transporte],
      ["Duración",     duracion],
      ["Agencia",      agencia],
      ["Personas",     personas],
      ["Recomendación",recomend]
    ].forEach(([dtTxt, ddTxt]) => {
      if (!ddTxt) return;
      const dt = Object.assign(document.createElement("dt"), { textContent: dtTxt });
      const dd = Object.assign(document.createElement("dd"), { textContent: ddTxt });
      dl.append(dt, dd);
    });
    sec.appendChild(dl);

    if (inicioHTML) sec.appendChild(this._htmlToSection(inicioHTML));
    if (refsHTML)   sec.appendChild(this._htmlToSection(refsHTML));
    if (hitosHTML)  sec.appendChild(this._htmlToSection(hitosHTML));

    /* ── Mapa dinámico ─────────────────────────────────────── */
    const mapHolder = document.createElement("section");
    mapHolder.appendChild(
      Object.assign(document.createElement("h5"), { textContent: "Mapa de la ruta" })
    );
    sec.append(mapHolder);

    /* ── Altimetría + planimetría ──────────────────────────── */
    let svgUrl = nodo.querySelector("altimetria")?.getAttribute("archivo")  || "";
    let kmlUrl = nodo.querySelector("planimetria")?.getAttribute("archivo") || "";
    if (svgUrl && !/^https?:\/\//i.test(svgUrl)) svgUrl = `xml/${svgUrl}`;
    if (kmlUrl && !/^https?:\/\//i.test(kmlUrl)) kmlUrl = `xml/${kmlUrl}`;

    if (svgUrl) this._cargaSVG(svgUrl, sec);
    this._initMap(mapHolder, coords, kmlUrl);

    /* ── Añadimos la ruta al documento ─────────────────────── */
    this.$contenedor.append(sec);
  }

  _htmlToSection (html) {
    const s = document.createElement("section");
    s.innerHTML = html;
    return s;
  }

  /* ---------------- Altimetría SVG ------------------------- */
  _cargaSVG (url, padre) {
    fetch(url)
      .then(r => r.ok ? r.text() : Promise.reject(r.status))
      .then(txt => {
        const svg = new DOMParser().parseFromString(txt, "image/svg+xml")
                                   .documentElement;
        const fig = document.createElement("figure");
        fig.append(
          Object.assign(document.createElement("figcaption"), { textContent: "Altimetría" }),
          svg
        );
        padre.appendChild(fig);
      })
      .catch(err => console.warn("No se pudo cargar SVG:", url, err));
  }

  /* ---------------- Mapa de Google ------------------------- */
  _initMap (holder, coords, kmlUrl) {
    const map = new google.maps.Map(holder, {
      zoom: 14,
      center: coords[0] || { lat: 0, lng: 0 },
      mapTypeId: "roadmap"
    });

    if (kmlUrl) {
      const layer = new google.maps.KmlLayer({ url: kmlUrl, map });
      layer.addListener("status_changed", () => {
        if (layer.getStatus() !== google.maps.KmlLayerStatus.OK) {
          new google.maps.Polyline({ path: coords, strokeColor: "#FF0000", strokeWeight: 4, map });
        }
      });
    } else if (coords.length) {
      new google.maps.Polyline({ path: coords, strokeColor: "#FF0000", strokeWeight: 4, map });
    }
  }
}

/* ░░░ Arranque ░░░ */
$(document).ready(() => {
  new RutasCompletas("AIzaSyC6j4mF6blrc4kZ54S6vYZ2_FpMY9VzyRU");
});
