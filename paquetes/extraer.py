# Importar las clases y funciones necesarias de la biblioteca Transformers
from transformers import AutoTokenizer, AutoModelForTokenClassification

# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys, json

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"

# Definir el mapeo de etiquetas POS
tags = {
    7: "NOUN",
    11: "PROPN",
    15: "VERB"
}

# Cargar el modelo BERT y su tokenizador
model_name = "mrm8488/bert-spanish-cased-finetuned-pos-16-tags"
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForTokenClassification.from_pretrained(model_name)

# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))
# text = "Esto es una prueba de texto para librerías de NLP ChileAtiende tíldés Ñuñoa."

# Tokenizar el texto completo
tokens = tokenizer.tokenize(text)
total_tokens = len(tokens)

# Parámetros para la segmentación
max_segment_size = 480
current_segment = []
segments = []

# Construir segmentos asegurándose de que las palabras no se dividan
for i, token in enumerate(tokens):
    if len(current_segment) + len(token) <= max_segment_size:
        current_segment.append(token)
    else:
        segments.append(current_segment)
        current_segment = [token]

    if i == total_tokens - 1:
        segments.append(current_segment)

# Función para filtrar verbos y sustantivos y retornar objetos
def extract_verbs_and_nouns(segment):
    segment_text = tokenizer.convert_tokens_to_string(segment)
    tokens = tokenizer(segment_text, return_tensors="pt", truncation=True, padding=True, max_length=max_segment_size)
    outputs = model(**tokens).logits
    predictions = outputs.argmax(2).tolist()

    current_word = ""
    current_entity = ""

    verb_noun_objects = []
    for token, label in zip(tokens["input_ids"][0], predictions[0]):
        if label in [7, 11, 15]:        
            subtoken = tokenizer.decode([token])
            
            if subtoken.startswith("##"):  # Subtoken de palabra dividida
                current_word += subtoken[2:]  # Eliminar "##" del subtoken y agregarlo a la palabra actual
            else:
                if current_word:
                    verb_noun_objects.append({"word": tokenizer.decode([token]), "entity": tags[label]})
                current_word = subtoken
                current_entity = tags[label]

    if current_word:  # Asegurarse de agregar la última palabra
        verb_noun_objects.append({"word": tokenizer.decode([token]), "entity": tags[label]})
        
    return verb_noun_objects

# Procesar y mostrar los verbos y sustantivos en cada segmento
output = []
for segment in segments:
    segment_text = tokenizer.convert_tokens_to_string(segment)
    lemmatized_text = lemmatize_verbs_with_bert(segment_text)
    verb_noun_segment = extract_verbs_and_nouns(lemmatized_text)
    output.extend(verb_noun_segment)


# Filtrar objetos no deseados
filtered_output = [item for item in output if item['word'] != "[UNK]" and item['word'] != "[SEP]" and item['word'] != "[PAD]" and item['word'] != "[CLS]" and item['word'] != "[MASK]"]

# Convertir el resultado filtrado en una cadena JSON
json_output = json.dumps({"resultados": filtered_output}, ensure_ascii=False)
print(json_output)
