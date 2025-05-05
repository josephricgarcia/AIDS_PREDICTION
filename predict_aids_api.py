from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from tensorflow.keras.models import load_model
import pickle

app = Flask(__name__)

# Load model and scaler
model = load_model("aids_model.keras")
with open("scaler.pkl", "rb") as f:
    scaler = pickle.load(f)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        input_data = pd.DataFrame({
            "age": [float(data['age'])],
            "wtkg": [float(data['weight'])],
            "homo": [int(data['homo'])],
            "drugs": [int(data['drugs'])],
            "oprior": [int(data['oprior'])],
            "z30": [int(data['z30'])],
            "gender": [int(data['gender'])],
            "str2": [int(data['str2'])],
            "symptom": [int(data['symptom'])],
            "treat": [int(data['treat'])],
            "offtrt": [int(data['offtrt'])]
        })
        input_data[["age", "wtkg"]] = scaler.transform(input_data[["age", "wtkg"]])
        probability = model.predict(input_data, verbose=0).flatten()[0]
        infected = 1 if probability > 0.5 else 0
        return jsonify({"probability": float(probability), "infected": int(infected)})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)