# Importar las clases y funciones necesarias de la biblioteca Transformers
from transformers import AutoTokenizer, AutoModelForTokenClassification
from transformers import pipeline

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

# Crear un procesador NER (Named Entity Recognition) utilizando el modelo "mrm8488/bert-spanish-cased-finetuned-pos-16-tags"
nlp_ner = pipeline("ner", model=model, tokenizer=tokenizer)

# Leer el contenido del archivo pasado como argumento en la línea de comandos
text = (file_get_contents(sys.argv[1]))

# Dividir el texto en segmentos de no más de 512 tokens
segment_size = 512
segments = [text[i:i + segment_size] for i in range(0, len(text), segment_size)]

# Función para filtrar verbos y sustantivos y retornar objetos
def extract_verbs_and_nouns(segment):
    tokens = tokenizer(segment, return_tensors="pt", truncation=True, padding=True)
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
    verb_noun_segment = extract_verbs_and_nouns(segment)
    output.extend(verb_noun_segment)

  # Filtrar objetos no deseados
filtered_output = [item for item in output if item['word'] != "[UNK]" and item['word'] != "[SEP]" and item['word'] != "[PAD]" and item['word'] != "[CLS]" and item['word'] != "[MASK]"]

# Convertir el resultado filtrado en una cadena JSON
json_output = json.dumps({"resultados": filtered_output}, ensure_ascii=False)
print(json_output)