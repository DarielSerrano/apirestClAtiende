# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys
sys.path.append('/var/www/html/apirestClAtiende/librerias')

import json, stanza, re

# Descargar y cargar el modelo de procesamiento de lenguaje
stanza.download('es', package='ancora', processors='tokenize,mwt,pos,lemma', verbose=True,model_dir="stanza_resources") 
stNLP = stanza.Pipeline(processors='tokenize,mwt,pos,lemma', lang='es', use_gpu=True, model_dir="stanza_resources") 

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"{pathfile}"
    
def correccion_tildes(text):
    # Mapeo de caracteres con tilde a sus versiones sin tilde
    replace_map = {
        'á': 'a', 'é': 'e', 'í': 'i', 'ó': 'o', 'ú': 'u',
        'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U',
    }
    
    # Reemplazar los caracteres con tilde por sus versiones sin tilde
    updated_text = text.translate(str.maketrans(replace_map))
    
    return updated_text    

# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

# Definición de una función para manipular el texto (eliminar espacios y saltos de línea)
WHITESPACE_HANDLER = lambda k: re.sub('\s+', ' ', re.sub('\n+', ' ', k.strip()))

# Aplicar la función de manipulación de espacios y saltos de línea al texto
text = WHITESPACE_HANDLER(text)

# Procesar el texto utilizando Stanza para obtener las palabras lematizadas y sus clasificaciones
doc = stNLP(text)

# Obtener las palabras lematizadas y sus clasificaciones (upos) del documento
lemmatized_words = []
for sent in doc.sentences:
    for word in sent.words:
        lemmatized_words.append({
            "lemma": word.lemma,
            "upos": word.upos
        })

# Filtrar los elementos con clasificación "NOUN", "VERB" o "PROPN"
objetos_filtrados = []
for elemento in lemmatized_words:
    if elemento["upos"] in ["NOUN", "VERB", "PROPN"]:
        objetos_filtrados.append({
            "palabra": correccion_tildes(elemento["lemma"]),
            "clasificacion": elemento["upos"]
        })

# Crear un objeto JSON con los elementos filtrados
resultado_json = {
    "resultados": objetos_filtrados
}

# Mostrar el resultado JSON
print(json.dumps(resultado_json, ensure_ascii=False))