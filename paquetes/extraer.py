# Importar las clases y funciones necesarias de la biblioteca Transformers
from transformers import AutoTokenizer, AutoModelForTokenClassification
from transformers import pipeline

# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import sys

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
# text = (file_get_contents(sys.argv[1]))
text = "El origen de este proyecto surge de la inquietud presentada el día 13 de julio del 2022 por la Sra. Rosa Bravo y el Sr. Carlos Salgado Jefe Subdepartamento Gestión de la Innovación, Departamento Gestión Estratégica y Estudios, División Planificación y Desarrollo para trabajar en proyectos junto a la Universidad.  El Instituto de Previsión Social (IPS) es un organismo  del estado cuya misión es la entrega beneficios y servicios previsionales y sociales, a través de su red de atención ChileAtiende, promoviendo la excelencia en su gestión y acercando de manera inclusiva el Estado a las personas, considerando a sus funcionarios y funcionarias como el principal capital de la institución. ChileAtiende es una red administrada por el IPS que busca acercar los servicios del Estado a las personas, entregando información de un conjunto de trámites de diferentes instituciones públicas, en un solo lugar. El Portal provee información acerca de trámites, leyes, beneficios, procedimientos, entre otros documentos de más de 29 instituciones públicas, incluyendo al propio IPS.  La atención a clientes se realiza a través de distintos canales de información, tales como sitio web, sucursales ChileAtiende, call center 101, módulos express ChileAtiende, atención móvil y redes sociales. Los documentos almacenados pueden tratar de acceso a la salud pública, acceso a vivienda, inicio a estudios superiores, enfrentar la muerte de un familiar, iniciar la jubilación, quedar sin trabajo, ser migrante (vivir y trabajar en Chile), tener un hijo o hija o trámites por internet con Clave Única."

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