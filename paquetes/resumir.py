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
   
# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

# Definición de una función para manipular el texto (eliminar espacios y saltos de línea)
WHITESPACE_HANDLER = lambda k: re.sub('\s+', ' ', re.sub('\n+', ' ', k.strip()))

text = WHITESPACE_HANDLER(text)

summarizer = pipeline("summarization", model="csebuetnlp/mT5_multilingual_XLSum")

# Generate the summary using the summarizer
summary = summarizer(text, max_length=840, num_beams=4, no_repeat_ngram_size=2)

# Extract the summary text from the output
summary_text = summary[0]['summary_text']

# Perform a specific replacement in the generated summary
summary_text = summary_text.replace("A continuación, ", "El siguiente documento trata de: ")

# Convert the summary to JSON format
result_json = json.dumps({"resumen": summary_text}, ensure_ascii=False)

# Print the JSON result
print(result_json)