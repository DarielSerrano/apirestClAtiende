from transformers import BertTokenizer, BertForSequenceClassification
import torch
import sys

# Función para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return "no file found"

# Cargar el modelo y el tokenizador
model_name = "bert-large-uncased"
model = BertForSequenceClassification.from_pretrained(model_name, num_labels=20)  # 20 labels for your categories
tokenizer = BertTokenizer.from_pretrained(model_name)

# Clases o categorías posibles
classes = ["Jubilación", "Certificados", "Trabajo y cesantía", "Pareja y Familia", "Salud", "Chilenos en el exterior", "Deporte", "Previsión y seguridad laboral", "Discapacidad", "Vivienda", "Emprendimiento e innovación", "Transporte", "Consumidor", "Medioambiente", "Bonos", "Extranjeros en Chile", "Becas y créditos", "Pueblos originarios", "Educación", "Cultura y recreación"]  # Cambiar por tus categorías reales

# Leer el contenido del archivo pasado como argumento en la línea de comandos
texto_largo = file_get_contents(sys.argv[1])
if texto_largo == "no file found":
    print("Archivo no encontrado.")
    sys.exit(1)

# Tokenizar el texto y obtener IDs de tokens
inputs = tokenizer(texto_largo, return_tensors="pt", truncation=True, padding=True)
with torch.no_grad():
    outputs = model(**inputs)

# Obtener las probabilidades de las clases y etiqueta predicha
probs = torch.softmax(outputs.logits, dim=-1)
predicted_label = classes[probs.argmax()]

print(f"Texto: {texto_largo}")
print(f"Categoría Predicha: {predicted_label}")
print("Probabilidades por categoría:")
for i, category in enumerate(classes):
    print(f"{category}: {probs[0][i].item():.4f}")