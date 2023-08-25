from transformers import BertForSequenceClassification, BertTokenizer, Trainer, TrainingArguments
import json

# Cargar el modelo preentrenado y el tokenizador
model_name = "bert-base-uncased"
model = BertForSequenceClassification.from_pretrained(model_name, num_labels=20)
tokenizer = BertTokenizer.from_pretrained(model_name)

# Cargar tus datos etiquetados desde un archivo JSON
with open("datos_train.json", "r") as json_file:
    data = json.load(json_file)

# Tokenizar los textos
def tokenize_function(examples):
    return tokenizer(examples["text"], padding="max_length", truncation=True)

# Convertir los datos a un formato adecuado para el tokenizador
tokenized_datasets = [{"text": item["text"], "label": item["label"]} for item in data]
tokenized_datasets = tokenize_function(tokenized_datasets)

# Definir los argumentos de entrenamiento
training_args = TrainingArguments(
    output_dir="./resultados_train",
    evaluation_strategy="epoch",
    learning_rate=2e-5,
    per_device_train_batch_size=8,
    per_device_eval_batch_size=8,
    num_train_epochs=3,
    save_total_limit=2,
)

# Convertir los ejemplos tokenizados a formato Dataset
from datasets import Dataset

train_dataset = Dataset.from_dict(tokenized_datasets)
train_dataset.set_format(type='torch', columns=['input_ids', 'attention_mask', 'label'])

# Definir el Trainer y entrenar el modelo
trainer = Trainer(
    model=model,
    args=training_args,
    train_dataset=train_dataset,
)

trainer.train()