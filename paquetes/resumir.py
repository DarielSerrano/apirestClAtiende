# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys
sys.path.append('/var/www/html/apirestClAtiende/librerias')

import re, json
from transformers import pipeline

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

# Aplicar corrección de tildes al texto
text = correccion_tildes(text)

# Definición de una función para manipular el texto (eliminar espacios y saltos de línea)
WHITESPACE_HANDLER = lambda k: re.sub('\s+', ' ', re.sub('\n+', ' ', k.strip()))

# Aplicar la función de manipulación de espacios y saltos de línea al texto
text = WHITESPACE_HANDLER(text)

# Inicializar el modelo de resumen
summarizer = pipeline("summarization", model="csebuetnlp/mT5_multilingual_XLSum")

# Generar el resumen utilizando el modelo de resumen
summary = summarizer(text, max_length=840, num_beams=4, no_repeat_ngram_size=2)

# Extraer el texto del resumen del resultado
summary_text = summary[0]['summary_text']

# Realizar reemplazos específicos en el resumen generado
summary_text = summary_text.replace("A continuación, ", "El siguiente documento trata de: ")

# Aplicar corrección de tildes al resumen
summary_text = correccion_tildes(summary_text)

# Convertir el resumen a formato JSON
result_json = json.dumps({"resumen": summary_text}, ensure_ascii=False)

# Imprimir el resultado en formato JSON
print(result_json)
