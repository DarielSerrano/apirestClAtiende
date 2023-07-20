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


from google.cloud import language_v1
import os

# Establecer la variable de entorno con la ubicación del archivo de credenciales
os.environ["GOOGLE_APPLICATION_CREDENTIALS"] = "../key/pragmatic-byway-393406-ab498ee30544.json"

def classify(text, verbose=True):
    """Classify the input text into categories."""

    language_client = language_v1.LanguageServiceClient()

    document = language_v1.Document(
        content=text, type_=language_v1.Document.Type.PLAIN_TEXT
    )
    response = language_client.classify_text(request={"document": document})
    categories = response.categories

    result = {}

    for category in categories:
        # Turn the categories into a dictionary of the form:
        # {category.name: category.confidence}, so that they can
        # be treated as a sparse vector.
        result[category.name] = category.confidence

    if verbose:
        print(text)
        for category in categories:
            print("=" * 20)
            print("{:<16}: {}".format("category", category.name))
            print("{:<16}: {}".format("confidence", category.confidence))

    return result

classify("Hola, soy un ejemplo de texto en español. Estoy muy feliz y quiero compartirlo contigo. Google Cloud Natural Language API es increíble.")