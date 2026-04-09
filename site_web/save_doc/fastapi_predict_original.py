# fastapi_predict.py
# Démarrage : python -m uvicorn fastapi_predict:app --reload
#
# Même structure que api_hate_detection_fastapi.py du projet de référence :
#   PHP  →  curl_init('http://127.0.0.1:8000/predict')  →  FastAPI  →  .pkl  →  JSON

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import joblib
import pandas as pd
import os

app = FastAPI()

# Autorise les appels depuis PHP en local
app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://addiction.rf.gd", "http://addiction.rf.gd"],
    allow_methods=["POST"],
    allow_headers=["*"],
)

# ── Chargement du modèle au démarrage ────────────────────
MODEL_PATH = os.path.join(os.path.dirname(__file__), "model_alcool.pkl")
try:
    saved          = joblib.load(MODEL_PATH)
    MODEL          = saved['model']
    LABEL_ENCODERS = saved.get('label_encoders', {})
    IS_PIPELINE    = False
    print("Modèle chargé")
except FileNotFoundError:
    MODEL = None
    print("ERREUR : model_alcool.pkl introuvable")

# ── Schéma des données (équivalent de ['message'] dans le projet de référence)
class StudentData(BaseModel):
    age:        int
    G1:         int
    G2:         int
    G3:         int
    freetime:   int
    goout:      int
    health:     int
    absences:   int
    studytime:  int
    Mjob:       str
    Fjob:       str
    reason:     str
    activities: str
    romantic:   str

# ── Route de vérification ─────────────────────────────────
@app.get("/")
def health():
    return {"status": "ok", "model_loaded": MODEL is not None}

# ── Route de prédiction ───────────────────────────────────
# Même URL /predict que dans le projet de référence : $apiUrl = '.../predict'
@app.post("/predict")
def predict(data: StudentData):
    if MODEL is None:
        return {"error": "Modèle non chargé"}

    etudiant = pd.DataFrame([{
        "G1": data.G1, "G2": data.G2, "G3": data.G3,
        "freetime": data.freetime, "goout": data.goout,
        "health": data.health, "absences": data.absences,
        "age": data.age, "studytime": data.studytime,
        "Mjob": data.Mjob, "Fjob": data.Fjob,
        "reason": data.reason, "activities": data.activities,
        "romantic": data.romantic,
    }])

    # Encodage LabelEncoder (même logique que l'entraînement)
    for col, le in LABEL_ENCODERS.items():
        try:
            etudiant[col] = le.transform(etudiant[col])
        except ValueError:
            etudiant[col] = 0

    raw   = MODEL.predict(etudiant)[0]
    score = int(round(max(1.0, min(5.0, float(raw)))))

    return {"score": score, "fiabilite": 57}