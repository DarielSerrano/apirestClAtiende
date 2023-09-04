# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys, json

# Función para corregir tildes en el texto
def correccion_tildes(text):
    # Mapeo de caracteres con tilde a sus versiones sin tilde
    replace_map = {
        'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u',
        'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U',
    }
    
    # Reemplazar los caracteres con tilde por sus versiones sin tilde
    updated_text = text.translate(str.maketrans(replace_map))
    
    return updated_text

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"{pathfile}"

# Leer el contenido del archivo pasado como argumento en la línea de comandos
raw_text = file_get_contents(sys.argv[1])

# Aplicar corrección de tildes al texto
text = correccion_tildes(raw_text)
    
# Definir palabras clave y etiquetas
keywords = {
    "Jubilacion": ["calculo de pension","pgu","tarjeta adulto mayor","tam","jubilacion","adulto mayor","jubilacion anticipada","pensionista","calculo de jubilacion","jubilacion por invalidez"],
    "Certificados": ["certificado","certificado de nacimiento","certificado de estudios","certificado de antecedentes","certificado de matrimonio","certificado de defuncion","certificado de residencia"   ],
    "Trabajo y Cesantia": ["seguro de cesantia","bolsa nacional de empleo","capacitacion","cesantia","trabajo","empleo","cv","seguro de desempleo","solicitud de empleo","entrevista de trabajo","formacion profesional"],
    "Pareja y Familia": ["matrimonio","familia","acuerdo de union civil","divorcio","subsidio familiar","pareja","adopcion","pension alimenticia","violencia domestica","custodia de hijos"],
    "Salud": ["inscripcion en fonasa","compra de bonos","auge-ges","ges","licencia medica","salud","hospital","clinica","consultorio","enfermedades cronicas","farmacias","seguro de salud privado"],
    "Chilenos en el Exterior": ["pasaporte","voto en el extranjero","bloqueo de pasaporte","representante en relaciones exteriores","exterior","embajada chilena","documentos para emigrantes","revalidacion de titulos","retorno a Chile"],
    "Deporte": ["deporte","elige vivir sano","registro nacional de deportistas y cazadores","organizaciones deportivas","competiciones deportivas","clubes deportivos","olimpiadas"],
    "Prevision y seguridad laboral": ["prevision","seguridad laboral","accidentes laborales","afiliacion a afp","afp","seguro de vida","derechos laborales","jubilacion por vejez","pension de invalidez"],
    "Discapacidad": ["discapacidad","inclusion","pension solidaria","registro nacional de la discapacidad","rnd","subsidio de discapacidad","situacion de discapacidad","asistencia a personas con discapacidad","integracion laboral","educacion inclusiva","subsidios para discapacitados"],
    "Vivienda": ["vivienda","subsidio a la vivienda","subsidio al arriendo","subsidio al mejoramiento","arriendo","arrendataria","arrendatario","hogares","rsh","compra de vivienda" ,"hipotecas" ,"viviendas sociales" ,"condominios"],
    "Emprendimiento e Innovacion": ["emprendimiento","innovacion","empresa en un dia","financiamiento ","pyme","plan de negocios" ,"aceleradoras de startups" ,"proteccion de la propiedad intelectual" ,"inversionistas"],
    "Transporte": ["transporte","permiso de circulacion","revision tecnica","hoja de vida del conductor","bloqueo de la licencia de conducir","licencia de conducir internacional" ,"transporte publico" ,"multas de trafico" ,"bicicletas publicas"],
    "Consumidor": ["consumidor","reclamos","comparadores","ley pro consumidor","sernac","derechos del consumidor" ,"reclamaciones de productos" ,"garantias de productos" ,"fraudes financieros"],
    "Medioambiente": ["medioambiente","educacion medioambiental","fondo concursable medioambiente","fondo de proteccion ambiental","fpa","reciclaje" ,"energias renovables" ,"conservacion de la naturaleza" ,"huella de carbono"],
    "Bonos": ["bonos","bono","aporte familiar permanente","bono al trabajo de la mujer","bono de alimentacion" ,"bono de escolaridad" ,"bono de navidad" ,"bono para madres solteras"],
    "Extranjeros en Chile": ["extranjeros en chile","prolongaciones","residencia definitiva","nacionalizacion","extranjero","visado de trabajo" ,"inmigracion ilegal" ,"refugio en Chile" ,"ley de extranjeria"],
    "Becas y Creditos": ["beca","credito","beca escolar","beca universitaria","gratuidad","fondo solidario","beca de postgrado" ,"credito hipotecario" ,"ayuda financiera estudiantil" ,"requisitos para becas"],
    "Pueblos Originarios": ["pueblo originario","acreditacion indigena","credito indigena","indigena","reconocimiento indigena" ,"territorios indigenas" ,"cultura indigena" ,"derechos de los pueblos originarios"],
    "Educacion": ["educacion","sala cuna","colegio","universidad","tne","alumno regular","educacion a distancia" ,"educacion tecnica" ,"educacion preescolar" ,"educacion continua"],
    "Cultura y Recreacion": ["cultura","recreacion","fondo concursable cultura","fondo concursable recreacion","museo","biblioteca","parque","turismo","zoologico","eventos culturales" ,"festivales de musica" ,"deportes acuaticos" ,"excursiones al aire libre"],
}

# Definir función para asignar etiquetas al texto
def label_text(text, keywords):
    best_label = None
    best_match_count = 0
    
    text_lower = text.lower()  # Convertir el texto a minúsculas
    
    for label, key_list in keywords.items():
        match_count = sum(1 for keyword in key_list if keyword.lower() in text_lower)  # Comparar en minúsculas
        if match_count > best_match_count:
            best_match_count = match_count
            best_label = label
            
    if best_label:
        return {"etiqueta": best_label}
    else:
        return {"etiqueta": "No categorizado"}

# Asignar una etiqueta al texto utilizando la función de etiquetado
result = label_text(text, keywords)

# Convertir el resultado a formato JSON
result_json = json.dumps(result, ensure_ascii=False)

# Imprimir el resultado en formato JSON
print(result_json)
