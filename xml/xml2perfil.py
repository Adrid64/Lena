import xml.etree.ElementTree as ET

def cargar_altimetria_ruta(ruta):
    puntos = []
    etiquetas = []

    # Punto inicial
    inicio = ruta.find('inicio')
    coord_inicio = inicio.find('coordenadas')
    altitud = float(coord_inicio.find('altitud').text)
    puntos.append((0.0, altitud))
    etiquetas.append("Inicio")

    # Hitos
    distancia_acumulada = 0.0
    for hito in ruta.find('hitos').findall('hito'):
        distancia = float(hito.find('distancia').text.strip())
        distancia_acumulada += distancia
        altitud = float(hito.find('coordenadas').find('altitud').text)
        nombre = hito.find('nombre').text.strip()
        puntos.append((distancia_acumulada, altitud))
        etiquetas.append(nombre)

    return puntos, etiquetas

def rects_colisionan(r1, r2):
    """ Devuelve True si los rectángulos r1 y r2 colisionan.
        Cada rectángulo r es un dict con keys: x, y, w, h. """
    return not (
        r1['x'] + r1['w'] < r2['x'] or
        r2['x'] + r2['w'] < r1['x'] or
        r1['y'] + r1['h'] < r2['y'] or
        r2['y'] + r2['h'] < r1['y']
    )
def generar_svg(puntos, etiquetas, nombre_archivo_svg):
    ancho = 800
    alto = 400
    margen = 20
    padding = 6
    extra_top = 30

    max_distancia = puntos[-1][0]
    altitudes = [p[1] for p in puntos]
    min_altitud = min(altitudes)
    max_altitud = max(altitudes)

    if min_altitud > 0:
        min_altitud = 0.0

    escala_x = (ancho - 2 * margen) / max_distancia if max_distancia != 0 else 1
    escala_y = (alto - 2 * margen) / (max_altitud - min_altitud) if max_altitud != min_altitud else 1

    puntos_svg = []
    coordenadas_svg = []
    for distancia, altitud in puntos:
        x = margen + distancia * escala_x
        y = extra_top + (alto - margen - (altitud - min_altitud) * escala_y)
        puntos_svg.append(f"{x},{y}")
        coordenadas_svg.append((x, y))

    puntos_svg.append(f"{margen + max_distancia * escala_x},{extra_top + (alto - margen)}")
    puntos_svg.append(f"{margen},{extra_top + (alto - margen)}")
    puntos_str = " ".join(puntos_svg)
    alto_total = alto + extra_top


    contenido_svg = (
        f'<svg viewBox="0 0 {ancho} {alto_total}" xmlns="http://www.w3.org/2000/svg">\n'
        '  <rect width="100%" height="100%" fill="white"/>\n'
        f'  <polygon points="{puntos_str}" fill="#FF8C00" stroke="black" stroke-width="2"/>\n'
    )

    y_cero = extra_top + (alto - margen - (0 - min_altitud) * escala_y)
    contenido_svg += (
        f'  <line x1="{margen}" x2="{ancho - margen}" '
        f'y1="{y_cero}" y2="{y_cero}" '
        'stroke="#00008B" stroke-width="4" stroke-dasharray="12,6" />\n'
    )
    contenido_svg += f'  <text x="{margen - 12}" y="{y_cero + 5}" font-size="14" fill="#00008B">0</text>\n'

    rects_ocupados = []
    for (x, y), texto in zip(coordenadas_svg, etiquetas):
        char_width = 6.5
        text_width = len(texto) * char_width + padding
        text_height = 20

        rect_x = x
        rect_y = y - 25

        if rect_x + text_width > ancho - margen:
            rect_x = (ancho - margen) - text_width
        if rect_x < margen:
            rect_x = margen

        let_rect = {'x': rect_x, 'y': rect_y, 'w': text_width, 'h': text_height}
        for anterior in rects_ocupados:
            while rects_colisionan(let_rect, anterior):
                let_rect['y'] -= (text_height + 4)
        rects_ocupados.append(dict(let_rect))

        contenido_svg += (
            f'  <rect x="{let_rect["x"]}" y="{let_rect["y"]}" '
            f'width="{let_rect["w"]}" height="{let_rect["h"]}" '
            'fill="black" fill-opacity="0.7"/>\n'
        )
        contenido_svg += (
            f'  <text x="{let_rect["x"] + padding/2}" y="{let_rect["y"] + 14}" '
            'font-size="12" fill="white">'
            f'{texto}</text>\n'
        )

    contenido_svg += '</svg>'

    with open(nombre_archivo_svg, 'w', encoding='utf-8') as archivo_svg:
        archivo_svg.write(contenido_svg)

    print(f'Se ha generado el archivo SVG: {nombre_archivo_svg}')


def main():
    archivo_xml = 'rutas.xml'

    try:
        tree = ET.parse(archivo_xml)
        root = tree.getroot()
        rutas = root.findall('ruta')
    except Exception as e:
        print("Error al procesar el archivo XML:", e)
        return

    for i, ruta in enumerate(rutas, start=1):
        try:
            puntos, etiquetas = cargar_altimetria_ruta(ruta)
            nombre_archivo_svg = f"altimetria_ruta{i}.svg"
            generar_svg(puntos, etiquetas, nombre_archivo_svg)
        except Exception as e:
            print(f"Error en la ruta {i}: {e}")

if __name__ == '__main__':
    main()
