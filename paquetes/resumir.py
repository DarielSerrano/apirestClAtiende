import sys, json
from transformers import pipeline

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"

# Función para generar un resumen abstractivo del texto
def generate_summary(text):
    summarizer = pipeline("summarization", model="facebook/bart-large-cnn")
    paragraphs = text.split("\n\n")  # Divide el texto en párrafos
    summaries = []

    for paragraph in paragraphs:
        if paragraph.strip():  # Ignora párrafos vacíos
            # Divide el párrafo en fragmentos de longitud manejable
            fragment_size = 500  # Puedes ajustar este valor según tus necesidades
            fragments = [paragraph[i:i+fragment_size] for i in range(0, len(paragraph), fragment_size)]

            fragment_summaries = []
            for fragment in fragments:
                summary = summarizer(fragment, max_length=150, min_length=50, do_sample=False)
                fragment_summaries.append(summary[0]['summary_text'])

            # Unir los fragmentos del párrafo en un solo resumen de párrafo
            paragraph_summary = " ".join(fragment_summaries)
            summaries.append(paragraph_summary)

    return "\n\n".join(summaries)

# Función para refinar el resumen inicial
def refine_summary(summary):
    summarizer = pipeline("summarization", model="Joemgu/mlong-t5-base-sumstew")
    refined_summary = summarizer(summary, max_length=300, min_length=50, do_sample=False)
    return refined_summary[0]['summary_text']

# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

# Generar el resumen inicial del texto
initial_summary = generate_summary(text)

# Refinar el resumen inicial
refined_summary = refine_summary(initial_summary)

result_json = json.dumps(refined_summary, ensure_ascii=False, indent=4)

# Imprimir el resultado JSON
print(result_json)