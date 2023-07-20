from google.cloud import language_v1
import os

# Establecer la variable de entorno con la ubicación del archivo de credenciales
os.environ["GOOGLE_APPLICATION_CREDENTIALS"] = "../key/pragmatic-byway-393406-ab498ee30544.json"

# Inicializar el cliente de Google Cloud Natural Language
client = language_v1.LanguageServiceClient()
def analizar_texto(texto):
    document = language_v1.Document(content=texto, type_=language_v1.Document.Type.PLAIN_TEXT, language="es")
    response = client.analyze_entities(
        request={"document": document, "encoding_type": encoding_type}
    )

    print(f"Sentimiento: {response.document_sentiment.score}")

    for entity in response.entities:
        print(f"Entidad: {entity.name}, Tipo: {language_v1.Entity.Type(entity.type_).name}, Salience: {entity.salience}")


# Ejemplo de texto en español a analizar
texto_a_analizar = "Hola, soy un ejemplo de texto en español. Estoy muy feliz y quiero compartirlo contigo. Google Cloud Natural Language API es increíble."

analizar_texto(texto_a_analizar) 

