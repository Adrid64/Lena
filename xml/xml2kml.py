import xml.etree.ElementTree as ET

class Kml:
    def __init__(self):
        self.raiz = ET.Element('kml', xmlns="http://www.opengis.net/kml/2.2")
        self.doc = ET.SubElement(self.raiz, 'Document')

    def addPlacemark(self, nombre, descripcion, long_lat_alt, modo_altitud):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = nombre
        ET.SubElement(pm, 'description').text = descripcion
        punto = ET.SubElement(pm, 'Point')
        ET.SubElement(punto, 'coordinates').text = long_lat_alt
        ET.SubElement(punto, 'altitudeMode').text = modo_altitud

    def addLineString(self, nombre, extrude, tesela, lista_coordenadas, modo_altitud, color, ancho):
        pm = ET.SubElement(self.doc, 'Placemark')
        ET.SubElement(pm, 'name').text = nombre
        ls = ET.SubElement(pm, 'LineString')
        ET.SubElement(ls, 'extrude').text = extrude
        ET.SubElement(ls, 'tessellate').text = tesela
        ET.SubElement(ls, 'coordinates').text = lista_coordenadas
        ET.SubElement(ls, 'altitudeMode').text = modo_altitud
        estilo = ET.SubElement(pm, 'Style')
        linea = ET.SubElement(estilo, 'LineStyle')
        ET.SubElement(linea, 'color').text = color
        ET.SubElement(linea, 'width').text = ancho

    def escribir(self, nombre_archivo_kml):
        arbol = ET.ElementTree(self.raiz)
        arbol.write(nombre_archivo_kml, encoding='utf-8', xml_declaration=True)

def xml_to_kml(xml_file, kml_file):
    try:
        tree = ET.parse(xml_file)
        root = tree.getroot()
    except ET.ParseError as e:
        print(f"Error al parsear el archivo XML: {e}")
        return
    except FileNotFoundError:
        print("El archivo XML no fue encontrado.")
        return

    rutas = root.findall('ruta')
    for idx, ruta in enumerate(rutas):
        try:
            nombre_ruta = ruta.find('nombre').text
            descripcion = ruta.find('descripcion').text

            kml = Kml()

            # Punto de inicio
            inicio = ruta.find('inicio')
            coord_inicio = inicio.find('coordenadas')
            long_lat_alt = f"{coord_inicio.find('longitud').text},{coord_inicio.find('latitud').text},{coord_inicio.find('altitud').text}"
            kml.addPlacemark("Inicio de la ruta", "Punto de salida", long_lat_alt, "absolute")

            # Hitos
            hitos = ruta.find('hitos').findall('hito')
            lista_coordenadas = long_lat_alt + " "
            for hito in hitos:
                nombre_hito = hito.find('nombre').text
                descripcion_hito = hito.find('descripcion').text
                coordenadas = hito.find('coordenadas')
                coord_str = f"{coordenadas.find('longitud').text},{coordenadas.find('latitud').text},{coordenadas.find('altitud').text}"
                kml.addPlacemark(nombre_hito, descripcion_hito, coord_str, "absolute")
                lista_coordenadas += coord_str + " "

            kml.addLineString(nombre="Recorrido de la ruta",
                              extrude="1", tesela="1",
                              lista_coordenadas=lista_coordenadas.strip(),
                              modo_altitud="absolute",
                              color="ff0000ff", ancho="3")

            ruta_id = idx + 1
            kml_output_name = f"{kml_file.replace('.kml', '')}_ruta{ruta_id}.kml"
            kml.escribir(kml_output_name)
            print(f"Archivo KML generado: {kml_output_name}")

        except Exception as e:
            print(f"Error procesando la ruta {idx+1}: {e}")

if __name__ == "__main__":
    xml_input = "rutas.xml"
    kml_output = "ruta.kml"
    xml_to_kml(xml_input, kml_output)
