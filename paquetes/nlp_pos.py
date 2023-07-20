""" from transformers import AutoTokenizer, AutoModelForTokenClassification

from transformers import pipeline

import sys
 
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"

nlp_pos = pipeline(
    "ner",
    model="mrm8488/bert-spanish-cased-finetuned-pos-16-tags",
    tokenizer=(
        'mrm8488/bert-spanish-cased-finetuned-pos-16-tags',  
        {"use_fast": False},
        {"model_max_length":512}
))


text = (file_get_contents(sys.argv[1]))
#print(nlp_pos(text))
print(nlp_pos("Hola, soy un ejemplo de texto en español. Estoy muy feliz y quiero compartirlo contigo. Google Cloud Natural Language API es increíble.")) """


from google.cloud import language_v1,Document,PartOfSpeech
import os

# Establecer la variable de entorno con la ubicación del archivo de credenciales
os.environ["GOOGLE_APPLICATION_CREDENTIALS"] = "../../key/pragmatic-byway-393406-ab498ee30544.json"


def extract_verbs_and_nouns(text):
    client = language_v1.LanguageServiceClient()

    # Configurar el tipo de análisis
    type_ = Document.Type.PLAIN_TEXT
    language = "es"
    document = {"content": text, "type": type_, "language": language}

    # Realizar la solicitud de análisis
    response = client.analyze_syntax(document=document)

    # Extraer verbos y sustantivos del resultado
    verbs = []
    nouns = []
    for token in response.tokens:
        if token.part_of_speech.tag == PartOfSpeech.Tag.VERB:
            verbs.append(token.text.content)
        elif token.part_of_speech.tag == PartOfSpeech.Tag.NOUN:
            nouns.append(token.text.content)

    return verbs, nouns

# Ejemplo de texto a analizar
texto_a_analizar = "Hola, soy un ejemplo de texto en español."

# Realizar el análisis y obtener los verbos y sustantivos
verbs, nouns = extract_verbs_and_nouns(texto_a_analizar)

# Imprimir los resultados
print("Verbos:", verbs)
print("Sustantivos:", nouns)