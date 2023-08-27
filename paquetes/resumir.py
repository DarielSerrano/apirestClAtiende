import sys, json
from transformers import pipeline

summarizer = pipeline("summarization", "joemgu/mlong-t5-large-sumstew")

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"
   
# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

summary = summarizer(text)[0]["summary_text"]

# Limpieza del resumen
cleaned_summary = summary.replace("Title:", "").replace("Summary:", "").strip()

# Convertir el resumen a formato JSON
result_json = json.dumps(cleaned_summary, ensure_ascii=False, indent=4)

# Imprimir el resultado JSON
print(result_json)
