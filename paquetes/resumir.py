import sys, json
import re
from transformers import pipeline

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"
   
# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

# Definición de una función para manipular el texto (eliminar espacios y saltos de línea)
WHITESPACE_HANDLER = lambda k: re.sub('\s+', ' ', re.sub('\n+', ' ', k.strip()))

text = WHITESPACE_HANDLER(text)

summary = pipeline("summarization", model="csebuetnlp/mT5_multilingual_XLSum")

# Realizar un reemplazo específico en el resumen generado
summary = summary.replace("A continuación, ", "El siguiente documento trata de: ")

# Convertir el resumen a formato JSON
result_json = json.dumps(summary, ensure_ascii=False, indent=4)

# Imprimir el resultado JSON
print(result_json)