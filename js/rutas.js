"use strict";

class RutasCompletas {
  constructor(apiKey) {
    this.apiKey        = apiKey;
    this.$fileInput    = $("main > section > section input[type=file]");
    this.$contenedor   = $("main > section > section + section");   // ⬅ contenedor como objeto jQuery

    this.$fileInput.on("change", e => this.leerXML(e.target.files[0]));
  }

  /* ───── Lectura del XML ───── */
  leerXML(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = evt => this.procesarXML(evt.target.result);
    reader.readAsText(file);
  }

  procesarXML(xmlText) {
    const xml = new DOMParser().parseFromString(xmlText, "application/xml");
    if (xml.querySelector("parsererror")) {
      console.error("Error al parsear el XML.");
      return;
    }
    this.$contenedor.empty();                       // ⬅ limpieza con jQuery
    Array.from(xml.querySelectorAll("ruta"))
         .forEach((nodo, i) => this._procesarRuta(nodo, i + 1));
  }

  /* ───── Procesa cada <ruta> ───── */
  _procesarRuta(nodo, idx) {
    const txt = sel => nodo.querySelector(sel)?.textContent.trim() || "";

    /* ─── Datos básicos ─── */
    const nombre      = txt("nombre") || `Ruta ${idx}`;
    const tipo        = txt("tipo");
    const transporte  = txt("transporte");
    const duracion    = txt("duracion");
    const agencia     = txt("agencia");
    const descripcion = txt("descripcion");
    const personas    = txt("personas");
    const recomend    = txt("recomendacion");

    /* ─── Bloque INICIO ─── */
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

    /* ─── Bloque REFERENCIAS ─── */
    const refs = Array.from(nodo.querySelectorAll("referencias > referencia"))
                      .map(r => r.textContent.trim());
    const refsHTML = refs.length
      ? `<h5>Referencias</h5>
         <ul>${refs.map(u => {
  const visible = u.length > 40 ? u.slice(0, 25) + "…" : u;
  return `<li><a href="${u}" target="_blank">${visible}</a></li>`;
}).join("")}</ul>
`
      : "";

    /* ─── Bloque HITOS ─── */
    const hitos = Array.from(nodo.querySelectorAll("hitos > hito")).map(h => {
      const n   = h.querySelector("nombre")?.textContent.trim() || "";
      const d   = h.querySelector("descripcion")?.textContent.trim() || "";
      const km  = h.querySelector("distancia")?.textContent.trim() || "";
      const lat = h.querySelector("coordenadas>latitud")?.textContent.trim() || "";
      const lng = h.querySelector("coordenadas>longitud")?.textContent.trim() || "";
      const alt = h.querySelector("coordenadas>altitud")?.textContent.trim() || "";
      return `<li>${n} (${km} km)${d}Coords: (${lat}, ${lng}), alt ${alt} m</li>`;
    });
    const hitosHTML = hitos.length ? `<h5>Hitos</h5><ul>${hitos.join("")}</ul>` : "";

    /* ─── Coordenadas para el mapa ─── */
    const coords = [];
    if (ini) coords.push({
      lat: parseFloat(ini.querySelector("coordenadas>latitud")?.textContent),
      lng: parseFloat(ini.querySelector("coordenadas>longitud")?.textContent)
    });
    nodo.querySelectorAll("hitos > hito").forEach(h => {
      coords.push({
        lat: parseFloat(h.querySelector("coordenadas>latitud")?.textContent),
        lng: parseFloat(h.querySelector("coordenadas>longitud")?.textContent)
      });
    });

    /* ─── Sección principal de la ruta ─── */
    const sec = document.createElement("section");

    /*   Encabezado de la ruta   */
    sec.append(
      Object.assign(document.createElement("h4"), { textContent: nombre }),
      Object.assign(document.createElement("p"),  { textContent: descripcion })
    );

    /*   Lista de detalles <dl>   */
    const dl = document.createElement("dl");
    [["Tipo",tipo],["Transporte",transporte],["Duración",duracion],
     ["Agencia",agencia],["Personas",personas],["Recomendación",recomend]]
      .forEach(([dtT, ddT]) => {
        if (!ddT) return;
        const dt = Object.assign(document.createElement("dt"), { textContent: dtT });
        const dd = Object.assign(document.createElement("dd"), { textContent: ddT });
        dl.append(dt, dd);
      });
    sec.appendChild(dl);

    /*   Secciones auxiliares   */
    if (inicioHTML) sec.appendChild(this._htmlToSection(inicioHTML));
    if (refsHTML)   sec.appendChild(this._htmlToSection(refsHTML));
    if (hitosHTML)  sec.appendChild(this._htmlToSection(hitosHTML));

    /* ─── Mapa + altimetría ─── */
    const mapHolder = document.createElement("section");

    /*  ⬇ Encabezado exigido para el mapa */
    const h5Mapa = Object.assign(document.createElement("h5"), { textContent: "Mapa de la ruta" });
    mapHolder.appendChild(h5Mapa);

    sec.append(mapHolder);

    /*   Archivos externos   */
    let svgUrl = nodo.querySelector("altimetria")?.getAttribute("archivo")  || "";
    let kmlUrl = nodo.querySelector("planimetria")?.getAttribute("archivo") || "";
    if (svgUrl && !svgUrl.startsWith("http")) svgUrl = `xml/${svgUrl}`;
    if (kmlUrl && !kmlUrl.startsWith("http")) kmlUrl = `xml/${kmlUrl}`;

    if (svgUrl) this._cargaSVG(svgUrl, sec);
    this._initMapDinámico(mapHolder, coords, kmlUrl);

    /* ─── Insertamos la ruta en la página ─── */
    this.$contenedor.append(sec);                     // ⬅ usando jQuery
  }

  /* Convierte un bloque HTML a <section> */
  _htmlToSection(html) {
    const s = document.createElement("section");
    s.innerHTML = html;
    return s;
  }

  /* Carga altimetría SVG */
  _cargaSVG(url, padre) {
    fetch(url)
      .then(r => (r.ok ? r.text() : Promise.reject(r.status)))
      .then(txt => {
        const svg = new DOMParser().parseFromString(txt, "image/svg+xml").documentElement;
        const fig = document.createElement("figure");
        fig.append(Object.assign(document.createElement("figcaption"), { textContent: "Altimetría" }),
                   svg);
        padre.appendChild(fig);
      })
      .catch(err => console.warn("No se pudo cargar SVG:", url, err));
  }

  /* Inicializa el mapa + KML / Polyline */
  _initMapDinámico(holder, coords, kmlUrl) {
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

/* ───── Arranque ───── */
$(document).ready(() => {
  new RutasCompletas("AIzaSyC6j4mF6blrc4kZ54S6vYZ2_FpMY9VzyRU");
});
