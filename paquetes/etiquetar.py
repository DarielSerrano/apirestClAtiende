import torch, sys
from transformers import BertForSequenceClassification, BertTokenizer

# Cargar el modelo entrenado
model_path = "./results"  # Ruta donde se guardó el modelo
model = BertForSequenceClassification.from_pretrained(model_path)

# Cargar el tokenizador
tokenizer = BertTokenizer.from_pretrained("bert-base-uncased") 

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"
    
# Clases o categorías posibles
classes = ["Jubilacion","Certificados","Trabajo y Cesantia","Pareja y Familia","Salud","Chilenos en exterior",
           "Deporte","Prevision y seguridad laboral","Discapacidad","Vivienda","Emprendimiento e innovacion",
           "Transporte","Consumidor","Medioambiente","Bonos","Extranjeros en Chile","Becas y creditos","Pueblos originarios",
           "Educacion","Cultura y recreacion"]

# Texto que deseas clasificar
texto_largo = (file_get_contents(sys.argv[1]))

# Tokenizar el texto y obtener las probabilidades de categoría
inputs = tokenizer(texto, return_tensors="pt", truncation=True, padding=True)
with torch.no_grad():
    outputs = model(**inputs)

# Obtener las probabilidades de las categorías
probs = torch.softmax(outputs.logits, dim=-1)

# Imprimir las probabilidades por categoría
for i, prob in enumerate(probs[0]):
    print(f"{classes[i]}: {prob.item():.4f}")