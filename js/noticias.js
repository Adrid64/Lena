class Noticias {
  constructor() {
  }

  leerArchivoTexto(files) {
    const archivo = files[0];
    if (!archivo) {
      alert("No se ha seleccionado ningún archivo.");
      return;
    }

    const tipoTexto = /text.*/;
    if (!archivo.type.match(tipoTexto)) {
      alert("Por favor, selecciona un archivo de texto válido (.txt).");
      return;
    }

    const lector = new FileReader();
    lector.onload = () => {
      this.procesarContenido(lector.result);
    };
    lector.onerror = () => {
      alert("Hubo un error al leer el archivo.");
    };
    lector.readAsText(archivo);
  }

  procesarContenido(contenido) {
    const main = document.querySelector("main"); 
    const lineas = contenido.split("\n");
    lineas.forEach((linea) => {
      if (linea.trim()) {
        const partes = linea.split("_");
        if (partes.length === 3) {
          const [titular, entradilla, autor] = partes.map(item => item.trim());
          this.crearNoticiaHTML(titular, entradilla, autor, main);
        } else {
          console.log(`Línea incorrecta: ${linea}`);
        }
      }
    });
  }

  agregarNoticia() {
    const titular = document.getElementById("titularInput").value.trim();
    const entradilla = document.getElementById("entradillaInput").value.trim();
    const autor = document.getElementById("autorInput").value.trim();

    if (titular && entradilla && autor) {
      this.crearNoticiaHTML(titular, entradilla, autor);
    } else {
      alert("Por favor, completa todos los campos.");
    }
  }

  crearNoticiaHTML(titular, entradilla, autor, contenedor = document.querySelector("main")) {
    const articulo = document.createElement("article");
    articulo.innerHTML = `
      <header><h3>${titular}</h3></header>
      <p>${entradilla}</p>
      <footer>Escrito por: ${autor}</footer>
    `;
    contenedor.appendChild(articulo);
  }
}

const noticias = new Noticias();
