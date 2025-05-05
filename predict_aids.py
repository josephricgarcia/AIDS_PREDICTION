from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
from tensorflow.keras.models import load_model
import pickle
import logging

app = Flask(__name__)

# Setup logging
logging.basicConfig(
    filename='predict_aids_api.log',
    level=logging.DEBUG,
    format='%(asctime)s %(levelname)s: %(message)s'
)

# Load model and scaler
try:
    model = load_model("aids_model.keras")
    logging.info("Successfully loaded model: aids_model.keras")
except Exception as e:
    logging.error("Failed to load model: %s", str(e))
    raise Exception(f"Failed to load model: {str(e)}")

try:
    with open("scaler.pkl", "rb") as f:
        scaler = pickle.load(f)
    logging.info("Successfully loaded scaler: scaler.pkl")
except Exception as e:
    logging.error("Failed to load scaler: %s", str(e))
    raise Exception(f"Failed to load scaler: {str(e)}")

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        logging.info("Received input data: %s", data)

        # Validate all required fields are present
        required_fields = ["age", "weight", "homo", "drugs", "oprior", "z30", "gender", "str2", "symptom", "treat", "offtrt"]
        for field in required_fields:
            if field not in data:
                logging.error("Missing required field: %s", field)
                return jsonify({"error": f"Missing required field: {field}"}), 400

        # Create input dataframe
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
        logging.info("Input dataframe created successfully.")

        # Scale continuous features
        input_data[["age", "wtkg"]] = scaler.transform(input_data[["age", "wtkg"]])
        logging.info("Successfully scaled features: age, wtkg")

        # Predict
        probability = model.predict(input_data, verbose=0).flatten()[0]
        infected = 1 if probability > 0.5 else 0
        logging.info("Prediction successful: probability=%s, infected=%s", probability, infected)

        return jsonify({"probability": float(probability), "infected": int(infected)})
    except ValueError as e:
        logging.error("Invalid input format: %s", str(e))
        return jsonify({"error": f"Invalid input format: {str(e)}"}), 400
    except Exception as e:
        logging.error("Prediction failed: %s", str(e))
        return jsonify({"error": f"Prediction failed: {str(e)}"}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)