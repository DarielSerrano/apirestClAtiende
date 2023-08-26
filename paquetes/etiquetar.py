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
        return f"no file found: {pathfile}"

# Leer el contenido del archivo pasado como argumento en la línea de comandos
raw_text = file_get_contents(sys.argv[1])

# Aplicar corrección de tildes al texto
text = correccion_tildes(raw_text)
    
# Definir palabras clave y etiquetas
keywords = {
    "Jubilacion": ["cálculo de pensión","pgu","tarjeta adulto mayor","tam","jubilación","adulto mayor"],
    "Certificados": ["certificado","certificado de nacimiento","certificado de estudios","certificado de antecedentes"],
    "Trabajo y Cesantia": ["seguro de cesantía","bolsa nacional de empleo","capacitación","cesantía","trabajo","empleo","cv"],
    "Pareja y Familia": ["matrimonio","familia","acuerdo de unión civil","divorcio","subsidio familiar"],
    "Salud": ["inscripción en fonasa","compra de bonos","auge-ges","ges","licencia médica","salud","hospital","clinica","consultorio"],
    "Chilenos en el Exterior": ["pasaporte","voto en el extranjero","bloqueo de pasaporte","representante en relaciones exteriores","exterior"],
    "Deporte": ["deporte","elige vivir sano","registro nacional de deportistas y cazadores","organizaciones deportivas"],
    "Previsión y seguridad laboral": ["previsión","seguridad laboral","accidentes laborales","afiliación a afp","afp"],
    "Discapacidad": ["discapacidad","inclusión","pensión solidaria","registro nacional de la discapacidad","rnd","subsidio de discapacidad","situación de discapacidad"],
    "Vivienda": ["vivienda","subsidio a la vivienda","subsidio al arriendo","subsidio al mejoramiento"],
    "Emprendimiento e Innovación": ["emprendimiento","innovación","empresa en un día","financiamiento ","pyme"],
    "Transporte": ["transporte","permiso de circulación","revisión técnica","hoja de vida del conductor","bloqueo de la licencia de conducir"],
    "Consumidor": ["consumidor","reclamos","comparadores ","ley pro consumidor","sernac"],
    "Medioambiente": ["medioambiente","educación medioambiental","fondo concursable medioambiente","fondo de protección ambiental","fpa"],
    "Bonos": ["bonos","bono","aporte familiar permanente","bono al trabajo de la mujer"],
    "Extranjeros en Chile": ["extranjeros en chile","permisos","prórrogas","residencia definitiva","nacionalización","extranjero"],
    "Becas y Créditos": ["beca","crédito","beca escolar","beca universitaria","gratuidad","fondo solidario"],
    "Pueblos Originarios": ["pueblo originario","acreditación indígena","crédito indígena","indígena"],
    "Educacion": ["educación","sala cuna","colegio","universidad","tne","alumno regular"],
    "Cultura y Recreacion": ["cultura","recreación","fondo concursable cultura","fondo concursable recreación","museo","biblioteca","parque","turismo","zoológico"],
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
        return {"label": best_label}
    else:
        return {"label": "No categorizado"}

result = label_text(text, keywords)
result_json = json.dumps(result, ensure_ascii=False, indent=4)

print(result_json)
