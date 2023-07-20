from transformers import AutoTokenizer, AutoModelForTokenClassification

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
print(nlp_pos(text))


from textblob import TextBlob

def extract_verbs_and_nouns(text):
    blob = TextBlob(text)
    tagged_words = blob.tags
    
    verbs = [word for word, pos in tagged_words if pos.startswith('VB')]
    nouns = [word for word, pos in tagged_words if pos.startswith('NN')]
    
    return verbs, nouns

text = "Hola, soy un ejemplo de texto en español."
verbs, nouns = extract_verbs_and_nouns(text)
print("Verbos:", verbs)
print("Sustantivos:", nouns)
