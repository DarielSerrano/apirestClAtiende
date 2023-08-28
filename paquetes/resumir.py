import sys, json
import re
from transformers import AutoTokenizer, AutoModelForSeq2SeqLM

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

# Nombre del modelo a utilizar
model_name = "csebuetnlp/mT5_multilingual_XLSum"

# Cargar el tokenizador y el modelo
tokenizer = AutoTokenizer.from_pretrained(model_name, legacy=False)
model = AutoModelForSeq2SeqLM.from_pretrained(model_name)

# Tokenizar el texto de entrada
input_ids = tokenizer(
    [WHITESPACE_HANDLER(text)],
    return_tensors="pt",
    padding="max_length",
    truncation=True,
    max_length=512
)["input_ids"]

# Generar un resumen con el modelo
output_ids = model.generate(
    input_ids=input_ids,
    max_length=400,
    no_repeat_ngram_size=2,
    num_beams=4
)[0]

# Decodificar el resumen generado en texto legible
summary = tokenizer.decode(
    output_ids,
    skip_special_tokens=True,
    clean_up_tokenization_spaces=False
)

# Realizar un reemplazo específico en el resumen generado
summary = summary.replace("A continuación, ", "El siguiente documento trata de: ")

# Convertir el resumen a formato JSON
result_json = json.dumps(summary, ensure_ascii=False, indent=4)

# Imprimir el resultado JSON
print(result_json)