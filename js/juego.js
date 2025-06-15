"use strict";
class Juego {
  constructor(preguntas) {
    this.preguntas = preguntas;
    this.$formulario = $("form");
    this.$botonEnviar = $("button");
    this.$resultado = $("main article p");
    this.inicializar();
  }

  inicializar() {
    this.generarPreguntas();

    // El botón estará siempre activo
    this.$botonEnviar.on("click", (e) => {
      e.preventDefault();
      this.evaluarRespuestas();
    });
  }

  generarPreguntas() {
  this.preguntas.forEach((pregunta, idx) => {
    const $section = $("<section></section>")
                     .append($("<h3></h3>").text(pregunta.texto));

    pregunta.opciones.forEach((opc, i) => {
      const $label = $("<label></label>")
      const $input = $("<input>", {
        type: "radio",
        name: `p${idx}`,
        value: i,
        required: i === 0
      });
      $label.append($input, ` ${opc}`);
      $section.append($label);
    });

    this.$formulario.append($section);
  });
}

  evaluarRespuestas() {
    // Validamos si todas están respondidas
    const todasRespondidas = this.preguntas.every((_, idx) =>
      this.$formulario.find(`input[name="p${idx}"]:checked`).length > 0
    );

    if (!todasRespondidas) {
      this.$resultado.text("Debes contestar todas las preguntas.")
      return;
    }

    let puntos = 0;
    this.preguntas.forEach((preg, idx) => {
      const seleccion = this.$formulario.find(`input[name="p${idx}"]:checked`).val();
      if (parseInt(seleccion, 10) === preg.correcta) puntos++;
    });

    this.$resultado
      .text(`Tu puntuación: ${puntos} / ${this.preguntas.length}`)
  }
}

// Inicialización
$(document).ready(() => {
  const preguntas = [
    {
      texto: "¿Cuál es el plato tradicional del Concejo de Lena?",
      opciones: ["Pote de castañes", "Arroz con leche", "Caldereta de cordero", "Morcilla y chorizo", "Garbanzos"],
      correcta: 2
    },
    {
      texto: "¿Qué postre tradicional se describe como cremoso con arroz, leche, azúcar y canela?",
      opciones: ["Frisuelos", "Arroz con leche", "Cuajada con miel", "Tarta de queso", "Natillas"],
      correcta: 1
    },
    {
      texto: "¿Qué embutidos son típicos y muy consumidos en el Concejo de Lena?",
      opciones: ["Salchichas y jamón", "Morcilla y chorizo", "Longaniza y salami", "Butifarra y sobrasada", "Verdinas con almejas"],
      correcta: 1
    },
    {
      texto: "¿Qué carne se prepara a la estaca durante las romerías en el Concejo de Lena?",
      opciones: ["Pollo a la brasa", "Cerdo asado", "Cordero a la estaca", "Ternera a la parrilla", "Truchas a la asturiana"],
      correcta: 2
    },
    {
      texto: "¿Qué plato es ideal para el otoño según la gastronomía asturiana?",
      opciones: ["Fabada asturiana", "Pote de castañes", "Sopa de pescado", "Ensalada mixta", "Verdinas con carne"],
      correcta: 1
    },
    {
      texto: "¿Qué postre asturiano se describe como crepes servidos con azúcar o miel?",
      opciones: ["Tarta de manzana", "Frisuelos", "Natillas", "Flan de huevo", "Cuajada con miel"],
      correcta: 1
    },
    {
      texto: "¿Con qué se acompaña la cuajada fresca en la gastronomía asturiana?",
      opciones: ["Chocolate", "Miel", "Nata", "Caramelo", "Arroz con leche"],
      correcta: 1
    },
    {
      texto: "¿Qué concurso nacional de callos se celebra en el Concejo de Lena?",
      opciones: ["La cuchara de oro", "La callada por respuesta", "El tenedor de plata", "El plato del año", "La guisandera Cristina Rey"],
      correcta: 1
    },
    {
      texto: "¿Quién es uno de los ganadores del concurso 'La Callada por Respuesta'?",
      opciones: ["Juan Pérez", "Lucía Fernández", "María García", "Pedro López", "Pilar Meana"],
      correcta: 1
    },
    {
      texto: "¿Qué otros platos son recomendados además de los ya mencionados?",
      opciones: ["Tortilla", "Garbanzos", "Lentejas", "Sopa", "Truchas a la asturiana"],
      correcta: 1
    }
  ];

  new Juego(preguntas);
});
