"use strict";

class RutasCompletas {
  constructor(apiKey) {
    this.apiKey = apiKey;
    // File input
    this.fileInput = document.querySelector("main > section > section input[type=file]");
    // Contenedor donde inyectamos rutas
    this.contenedor = document.querySelector("main > section > section + section");

    this.fileInput.addEventListener("change", e => this.leerXML(e.target.files[0]));
  }

  leerXML(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = evt => this.procesarXML(evt.target.result);
    reader.readAsText(file);
  }

  procesarXML(xmlText) {
    const xml = new DOMParser().parseFromString(xmlText, "application/xml");
    if (xml.querySelector("parsererror")) {
      console.error(" Error al parsear el XML.");
      return;
    }
    this.contenedor.innerHTML = ""; // limpieza
    Array.from(xml.querySelectorAll("ruta"))
      .forEach((nodo, i) => this._procesarRuta(nodo, i + 1));
  }

  _procesarRuta(nodo, idx) {
    // helper para textContent seguro
    const txt = sel => nodo.querySelector(sel)?.textContent.trim() || "";

    // Campos
    const nombre      = txt("nombre") || `Ruta ${idx}`;
    const tipo        = txt("tipo");
    const transporte  = txt("transporte");
    const duracion    = txt("duracion");
    const agencia     = txt("agencia");
    const descripcion = txt("descripcion");
    const personas    = txt("personas");
    const recomend    = txt("recomendacion");

    // Inicio
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

    // Referencias
    const refs = Array.from(nodo.querySelectorAll("referencias > referencia"))
                      .map(r => r.textContent.trim());
    const refsHTML = refs.length
      ? `<h5>Referencias</h5>
         <ul>${refs.map(u=>`<li><a href="${u}" target="_blank">${u}</a></li>`).join("")}</ul>`
      : "";

    // Hitos
    const hitos = Array.from(nodo.querySelectorAll("hitos > hito"))
      .map(h => {
        const n    = h.querySelector("nombre")?.textContent.trim() || "";
        const d    = h.querySelector("descripcion")?.textContent.trim() || "";
        const dist = h.querySelector("distancia")?.textContent.trim() || "";
        const lat  = h.querySelector("coordenadas>latitud")?.textContent.trim() || "";
        const lng  = h.querySelector("coordenadas>longitud")?.textContent.trim() || "";
        const alt  = h.querySelector("coordenadas>altitud")?.textContent.trim() || "";
        return `<li>
                  ${n} (${dist} km)<br>
                  ${d}<br>
                  Coords: (${lat},${lng}), alt ${alt} m
                </li>`;
      });
    const hitosHTML = hitos.length
      ? `<h5>Hitos</h5><ul>${hitos.join("")}</ul>`
      : "";

    // Coordenadas para trazar en el mapa
    const coords = [];
    if (ini) {
      coords.push({
        lat: parseFloat(ini.querySelector("coordenadas>latitud")?.textContent),
        lng: parseFloat(ini.querySelector("coordenadas>longitud")?.textContent)
      });
    }
    nodo.querySelectorAll("hitos > hito").forEach(h => {
      coords.push({
        lat: parseFloat(h.querySelector("coordenadas>latitud")?.textContent),
        lng: parseFloat(h.querySelector("coordenadas>longitud")?.textContent)
      });
    });

    // Creamos sección
    const sec = document.createElement("section");
    // Título y descripción
    const h4 = document.createElement("h4"); h4.textContent = nombre;
    const p  = document.createElement("p");  p.textContent = descripcion;
    sec.append(h4, p);

    // Detalles en <dl>
    const dl = document.createElement("dl");
    [["Tipo",tipo],["Transporte",transporte],["Duración",duracion],
     ["Agencia",agencia],["Personas",personas],["Recomendación",recomend]
    ].forEach(([dtT,ddT])=>{
      if (!ddT) return;
      const dt = document.createElement("dt"); dt.textContent = dtT;
      const dd = document.createElement("dd"); dd.textContent = ddT;
      dl.append(dt,dd);
    });
    sec.appendChild(dl);

    // Inicio, referencias, hitos
    if (inicioHTML) sec.appendChild(this._htmlToSection(inicioHTML));
    if (refsHTML)   sec.appendChild(this._htmlToSection(refsHTML));
    if (hitosHTML)  sec.appendChild(this._htmlToSection(hitosHTML));

    // Contenedor para mapa dinámico
    const mapHolder = document.createElement("section");
    sec.append(mapHolder);

    // URLs desde atributo "archivo"
    let svgUrl = nodo.querySelector("altimetria")?.getAttribute("archivo") || "";
    let kmlUrl = nodo.querySelector("planimetria")?.getAttribute("archivo") || "";

    // Ajustar prefijo si no es URL absoluta
    if (svgUrl && !svgUrl.startsWith("http")) {
      svgUrl = `xml/${svgUrl}`;
    }
    if (kmlUrl && !kmlUrl.startsWith("http")) {
      kmlUrl = `xml/${kmlUrl}`;
    }

    // Cargamos el SVG de altimetría (si existe)
    if (svgUrl) {
      this._cargaSVG(svgUrl, sec);
    }

    // Inicializamos mapa dinámico con la planimetría/KML o fallback a polilínea
    this._initMapDinámico(mapHolder, coords, kmlUrl);

    this.contenedor.appendChild(sec);
  }

  _htmlToSection(html) {
    const s = document.createElement("section");
    s.innerHTML = html;
    
    return s;
  }

  _cargaSVG(url, padre) {
    if (!url) return;
    fetch(url)
      .then(r => r.ok ? r.text() : Promise.reject(r.status))
      .then(texto => {
        const svgDoc = new DOMParser().parseFromString(texto, "image/svg+xml");
        const fig = document.createElement("figure");
        const cap = document.createElement("figcaption");
        cap.textContent = "Altimetría";
        fig.append(cap, svgDoc.documentElement);
        padre.appendChild(fig);
      })
      .catch(err => console.warn("No se pudo cargar SVG:", url, err));
  }

  _initMapDinámico(container, coords, kmlUrl) {
    const map = new google.maps.Map(container, {
      zoom: 14,
      center: coords[0] || { lat: 0, lng: 0 },
      mapTypeId: "roadmap"
    });

    if (kmlUrl) {
      const layer = new google.maps.KmlLayer({ url: kmlUrl, map });
      layer.addListener("status_changed", () => {
        if (layer.getStatus() !== google.maps.KmlLayerStatus.OK) {
          // Fallback a polilínea si el KML falla
          new google.maps.Polyline({
            path: coords,
            strokeColor: "#FF0000",
            strokeWeight: 4,
            map
          });
        }
      });
    } else if (coords.length) {
      new google.maps.Polyline({
        path: coords,
        strokeColor: "#FF0000",
        strokeWeight: 4,
        map
      });
    }
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const MI_API_KEY = "AIzaSyC6j4mF6blrc4kZ54S6vYZ2_FpMY9VzyRU";
  new RutasCompletas(MI_API_KEY);
});
