/* =========================================================
   Carrusel 
   ========================================================= */
"use strict";
class Carrusel {
  constructor() {
    /* Artículo que contiene el carrusel */
    this.$article = $("main > section:first-of-type > article");

    /* Slides = todas las imágenes del artículo */
    this.$slides = this.$article.children("img");

    /* Botones de navegación */
    this.$btnPrev = this.$article.children("button:first-child");
    this.$btnNext = this.$article.children("button:last-child");

    this.curSlide = 0;
    this.maxSlide = this.$slides.length - 1;

    this._initCarrusel();
  }

  _initCarrusel() {
    this._updateSlides();                          // posición inicial
    this.$btnNext.on("click", () => this._nextSlide());
    this.$btnPrev.on("click", () => this._prevSlide());
  }

  _updateSlides() {
    this.$slides.each((i, img) => {
      $(img).css("transform", `translateX(${100 * (i - this.curSlide)}%)`);
    });
  }

  _nextSlide() {
    this.curSlide = this.curSlide === this.maxSlide ? 0 : this.curSlide + 1;
    this._updateSlides();
  }

  _prevSlide() {
    this.curSlide = this.curSlide === 0 ? this.maxSlide : this.curSlide - 1;
    this._updateSlides();
  }
}

/* =========================================================
   Noticias 
   ========================================================= */
class Noticias {
  constructor(apiKey) {
    this.apiKey = apiKey;
    this.$container = $("main > section:nth-of-type(2) > article");
    this._cargarNoticias();
  }


  _nfc(txt) {
    /* Devuelve la cadena en forma compuesta NFC o '' */
    return (txt ?? "").normalize("NFC");
  }

  _cargarNoticias() {
    const endpoint = "https://newsdata.io/api/1/latest";
    const params = $.param({
      apikey: this.apiKey,
      q: "Lena, Asturias",
      language: "es",
      country: "es",
      size: 5
    });
    const url = `${endpoint}?${params}`;

    this.$container.text("Cargando noticias…");

    $.getJSON(url)
      .done(data => {
        if (data.status === "success" && Array.isArray(data.results) && data.results.length) {
          this._renderizarNoticias(data.results);
        } else {
          this.$container.empty().append(`
            <h3>Sin noticias</h3>
            <p>No hay noticias disponibles en este momento.</p>
          `);
        }
      })
      .fail((_, textStatus, errorThrown) => {
        console.error("Error al obtener noticias:", textStatus, errorThrown);
        this.$container.empty().append(`
          <h3>Error al cargar noticias</h3>
          <p>No se han podido recuperar las noticias.
             Vuelve a intentarlo dentro de unos minutos.</p>
        `);
      });
  }

  _renderizarNoticias(articulos) {
    this.$container.empty();
    this.$container.append("<h3>Últimas noticias sobre Lena</h3>");

    const $ul = $("<ul></ul>");

    articulos.forEach(art => {
      const title   = this._nfc(art.title)       || "Título no disponible";
      const link    = this._nfc(art.link)        || "#";
      const desc    = this._nfc(art.description) || "";
      const pubDate = art.pubDate
        ? new Date(art.pubDate).toLocaleDateString("es-ES", {
            day: "2-digit", month: "2-digit", year: "numeric"
          })
        : "";

      const $li = $("<li></li>");
      const $a  = $("<a></a>", {
                  href: link,
                  target: "_blank",
                  rel: "noopener"
                }).text(title);
      $li.append($a);

      if (pubDate) $li.append($("<p></p>").text(pubDate));
      if (desc)    $li.append($("<p></p>").text(desc));

      $ul.append($li);
    });

    this.$container.append($ul);
  }
}

$(document).ready(() => {
  new Carrusel();

  const NEWS_API_KEY = "pub_197f26f6e4e841d8ad135a3a5b357a39";
  new Noticias(NEWS_API_KEY);
});
