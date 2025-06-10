"use strict";

// ---------------------------------------------------
// 1) CARUSEL CLASS
// ---------------------------------------------------
class Carrusel {
  constructor() {
    // Seleccionamos el <article> dentro del primer <section> de <main>
    this.$article = $("main > section:first-of-type > article");

    // Todas las imágenes hijas de ese artículo
    this.$slides = this.$article.children("img");

    // Botones: primer botón = retroceder, segundo = avanzar
    this.$btnPrev = this.$article.children("button").eq(0);
    this.$btnNext = this.$article.children("button").eq(1);

    // Estado del carrusel
    this.curSlide = 0;
    this.maxSlide = this.$slides.length - 1;

    // Posicionar slides y enlazar eventos
    this._initCarrusel();
  }

  _initCarrusel() {
    // Posicionar inicialmente cada slide
    this._updateSlides();

    // Enlazar clics de navegación
    this.$btnNext.on("click", () => this._nextSlide());
    this.$btnPrev.on("click", () => this._prevSlide());
  }

  _updateSlides() {
    this.$slides.each((index, img) => {
      $(img).css("transform", `translateX(${100 * (index - this.curSlide)}%)`);
    });
  }

  _nextSlide() {
    if (this.curSlide === this.maxSlide) {
      this.curSlide = 0;
    } else {
      this.curSlide++;
    }
    this._updateSlides();
  }

  _prevSlide() {
    if (this.curSlide === 0) {
      this.curSlide = this.maxSlide;
    } else {
      this.curSlide--;
    }
    this._updateSlides();
  }
}

// ---------------------------------------------------
// 2) NOTICIAS CLASS 
// ---------------------------------------------------
class Noticias {
  constructor(apiKey) {
    this.apiKey = apiKey;

    // Seleccionamos el <article> dentro del segundo <section> de <main>
    this.$container = $("main > section:nth-of-type(2) > article");

    this._cargarNoticias();
  }

  _cargarNoticias() {
    const endpoint = "https://newsdata.io/api/1/latest";
    const params = $.param({
      apikey: this.apiKey,
      q: "Lena Asturias",
      language: "es",
      country: "es",
      size: 5
    });
    const url = `${endpoint}?${params}`;

    this.$container.text("Cargando noticias…");

    $.getJSON(url)
      .done((data) => {
        if (data.status === "success" && Array.isArray(data.results) && data.results.length) {
          this._renderizarNoticias(data.results);
        } else {
          this.$container.text("No hay noticias disponibles en este momento.");
        }
      })
      .fail((_, textStatus, errorThrown) => {
        console.error("Error al obtener noticias:", textStatus, errorThrown);
        this.$container.text("Error cargando noticias. Vuelva a intentarlo más tarde.");
      });
  }

  _renderizarNoticias(articulos) {
    this.$container.empty();

    this.$container.append("<h3>Últimas noticias sobre Lena</h3>");

    const $ul = $("<ul></ul>");

    articulos.forEach((art) => {
      const title = art.title || "Título no disponible";
      const link = art.link || "#";
      const desc = art.description || "";
      const pubDate = art.pubDate
        ? new Date(art.pubDate).toLocaleDateString("es-ES", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric"
          })
        : "";

      const $li = $("<li></li>");
      const $a = $(`<a></a>`).attr({
        href: link,
        target: "_blank",
        rel: "noopener"
      }).text(title);
      $li.append($a);

      if (pubDate) {
        const $pDate = $("<p></p>").text(pubDate);
        $li.append($pDate);
      }
      if (desc) {
        const $pDesc = $("<p></p>").text(desc);
        $li.append($pDesc);
      }

      $ul.append($li);
    });

    this.$container.append($ul);
  }
}

// ---------------------------------------------------
// 3) AL CARGAR EL DOM
// ---------------------------------------------------
$(document).ready(() => {
  new Carrusel();

  const NEWS_API_KEY = "pub_197f26f6e4e841d8ad135a3a5b357a39";
  new Noticias(NEWS_API_KEY);
});
