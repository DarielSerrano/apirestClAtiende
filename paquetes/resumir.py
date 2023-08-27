import sys, json
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

summarizer = pipeline("summarization", model="justinian336/salvadoran-news-summarizer-base-auto")

final_summary = summarizer(text, truncation=True)

# Convertir el resumen a formato JSON
result = {"Resumen": final_summary}
result_json = json.dumps(result, ensure_ascii=False, indent=4)

# Imprimir el resultado JSON
print(result_json)
