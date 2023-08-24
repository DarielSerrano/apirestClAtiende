# Importar las clases y funciones necesarias de la biblioteca Transformers
from transformers import AutoTokenizer, AutoModelForTokenClassification
from transformers import pipeline

# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys

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

    verb_noun_objects = []
    for token, label in zip(tokens["input_ids"][0], predictions[0]):
        if label in [7, 11, 15]:
            verb_noun_objects.append({"word": tokenizer.decode([token]), "entity": tags[label]})
    
    return verb_noun_objects

# Procesar y mostrar los verbos y sustantivos en cada segmento
for segment in segments:
    verb_noun_segment = extract_verbs_and_nouns(segment)
    for item in verb_noun_segment:
        print(f"Palabra: {item['word']}, Entidad: {item['entity']}")
    print()