from transformers import BertForSequenceClassification, BertTokenizer, Trainer, TrainingArguments

# Cargar el modelo preentrenado y el tokenizador
model_name = "bert-base-uncased"
model = BertForSequenceClassification.from_pretrained(model_name, num_labels=20)
tokenizer = BertTokenizer.from_pretrained(model_name)

# Cargar tus datos etiquetados
data = pd.read_csv("datos_train.csv")  # Asegúrate de tener una columna "text" y otra "label"

# Tokenizar los textos
def tokenize_function(examples):
    return tokenizer(examples["text"], padding="max_length", truncation=True)

tokenized_datasets = data.map(tokenize_function, batched=True)

# Definir los argumentos de entrenamiento
training_args = TrainingArguments(
    output_dir="/resultados_train", 
    evaluation_strategy="epoch",
    learning_rate=2e-5,
    per_device_train_batch_size=8,
    per_device_eval_batch_size=8,
    num_train_epochs=3,
    save_total_limit=2,
)

# Definir el Trainer y entrenar el modelo
trainer = Trainer(
    model=model,
    args=training_args,
    train_dataset=tokenized_datasets["train"],
    eval_dataset=tokenized_datasets["validation"],
)

trainer.train()
