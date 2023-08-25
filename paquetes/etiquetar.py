from transformers import LongformerTokenizer, LongformerForSequenceClassification

# Importar el módulo 'sys' para manejar argumentos de línea de comandos
import torch, sys, json

# Funcion para leer archivos por ruta
def file_get_contents(pathfile):
    try:
        with open(pathfile) as f:
            return f.read()
    except IOError:
        return f"no file found: {pathfile}"

# Cargar el modelo y el tokenizador
model_name = "allenai/longformer-base-4096"
model = LongformerForSequenceClassification.from_pretrained(model_name)
tokenizer = LongformerTokenizer.from_pretrained(model_name)

# Clases o categorías posibles
classes = ["Jubilación","Certificados","Trabajo y cesantía","Pareja y Familia","Salud","Chilenos en el exterior","Deporte","Previsión y seguridad laboral","Discapacidad","Vivienda","Emprendimiento e innovación","Transporte","Consumidor","Medioambiente","Bonos","Extranjeros en Chile","Becas y créditos","Pueblos originarios","Educación","Cultura y recreación"]  # Cambiar por tus categorías reales

# Leer el contenido del archivo pasado como argumento en la línea de comandos
texto_largo = (file_get_contents(sys.argv[1]))
# Texto largo que deseas clasificar
# texto_largo = "¿A quién está dirigido? Personas extranjeras en general: Tener 18 años al momento de hacer la solicitud, o 14 si cuenta con la autorización de sus padres, o de quienes estén a cargo de su cuidado personal (deberá presentar documento legal que acredite el cuidado). Ser titular de Residencia Definitiva vigente. Tener 5 años o más de residencia en Chile (este plazo se contará a partir de la fecha de estampado de la Residencia Temporal que dio origen a la Residencia Definitiva que mantiene vigente). Nacionalización calificada artículo 85 de la Ley Nº 21.325: También podrán solicitar la nacionalización las personas con Residencia Definitiva que acrediten 2 años de residencia continuada en Chile, y que tengan alguno de los siguientes vínculos con el país: Calidad de cónyuge de una persona chilena, a lo menos durante 2 años y cuyo matrimonio se encuentre inscrito en Chile, siempre que en el mismo período se cumpla que vivan en un hogar común (artículo 133 del Código Civil). Los parientes de chilenos o chilenas por consanguineidad, hasta el segundo grado inclusive, y adoptados o adoptadas por personas chilenas. Hijo o hija cuyo padre o madre, habiendo sido chileno, haya perdido la nacionalidad chilena con anterioridad a su nacimiento. Españoles: Convenio de doble nacionalidad: podrán optar a este los españoles y españolas nacidos en el territorio peninsular, Islas Baleares y Canarias. Para tal efecto, deberá presentar una declaración jurada notarial en la cual solicita acogerse al convenio de doble nacionalidad entre Chile y España "

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
    if i < len(probs[0]):
        print(f"{category}: {probs[0][i].item():.4f}")