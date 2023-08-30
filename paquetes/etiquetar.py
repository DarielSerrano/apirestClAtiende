# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys, json

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
    "Jubilacion": ["calculo de pension","pgu","tarjeta adulto mayor","tam","jubilacion","adulto mayor"],
    "Certificados": ["certificado","certificado de nacimiento","certificado de estudios","certificado de antecedentes"],
    "Trabajo y Cesantia": ["seguro de cesantia","bolsa nacional de empleo","capacitacion","cesantia","trabajo","empleo","cv"],
    "Pareja y Familia": ["matrimonio","familia","acuerdo de union civil","divorcio","subsidio familiar"],
    "Salud": ["inscripcion en fonasa","compra de bonos","auge-ges","ges","licencia medica","salud","hospital","clinica","consultorio"],
    "Chilenos en el Exterior": ["pasaporte","voto en el extranjero","bloqueo de pasaporte","representante en relaciones exteriores","exterior"],
    "Deporte": ["deporte","elige vivir sano","registro nacional de deportistas y cazadores","organizaciones deportivas"],
    "Prevision y seguridad laboral": ["prevision","seguridad laboral","accidentes laborales","afiliacion a afp","afp"],
    "Discapacidad": ["discapacidad","inclusion","pension solidaria","registro nacional de la discapacidad","rnd","subsidio de discapacidad","situacion de discapacidad"],
    "Vivienda": ["vivienda","subsidio a la vivienda","subsidio al arriendo","subsidio al mejoramiento"],
    "Emprendimiento e Innovacion": ["emprendimiento","innovacion","empresa en un dia","financiamiento ","pyme"],
    "Transporte": ["transporte","permiso de circulacion","revision tecnica","hoja de vida del conductor","bloqueo de la licencia de conducir"],
    "Consumidor": ["consumidor","reclamos","comparadores ","ley pro consumidor","sernac"],
    "Medioambiente": ["medioambiente","educacion medioambiental","fondo concursable medioambiente","fondo de proteccion ambiental","fpa"],
    "Bonos": ["bonos","bono","aporte familiar permanente","bono al trabajo de la mujer"],
    "Extranjeros en Chile": ["extranjeros en chile","permisos","prolongaciones","residencia definitiva","nacionalizacion","extranjero"],
    "Becas y Creditos": ["beca","credito","beca escolar","beca universitaria","gratuidad","fondo solidario"],
    "Pueblos Originarios": ["pueblo originario","acreditacion indigena","credito indigena","indigena"],
    "Educacion": ["educacion","sala cuna","colegio","universidad","tne","alumno regular"],
    "Cultura y Recreacion": ["cultura","recreacion","fondo concursable cultura","fondo concursable recreacion","museo","biblioteca","parque","turismo","zoologico"],
}

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

result = label_text(text, keywords)
result_json = json.dumps(result, ensure_ascii=False)

print(result_json)
