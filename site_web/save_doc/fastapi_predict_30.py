# fastapi_predict.py
# Démarrage : python -m uvicorn fastapi_predict:app --reload

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Literal
import joblib
import pandas as pd
import os

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://addiction.rf.gd", "http://addiction.rf.gd"],
    allow_methods=["POST"],
    allow_headers=["*"],
)

# ── Chargement du modèle ──────────────────────────────────────────────────────
MODEL_PATH = os.path.join(os.path.dirname(__file__), "model_alcohol.pkl")
try:
    saved           = joblib.load(MODEL_PATH)
    MODEL           = saved['model']
    LABEL_ENCODERS  = saved['label_encoders']
    FEATURE_ORDER   = saved['feature_order']
    print(f"Modèle chargé — features attendues : {len(FEATURE_ORDER)}")
except FileNotFoundError:
    MODEL = None
    print("ERREUR : model_alcool.pkl introuvable")

# ── Schéma des données ────────────────────────────────────────────────────────
class StudentData(BaseModel):
    # Numériques
    age:        int
    Medu:       int
    Fedu:       int
    traveltime: int
    studytime:  int
    failures:   int
    famrel:     int
    freetime:   int
    goout:      int
    health:     int
    absences:   int
    G1:         int
    G2:         int
    G3:         int
    # Catégorielles
    sex:        Literal['F', 'M']
    address:    Literal['U', 'R']
    famsize:    Literal['GT3', 'LE3']
    Pstatus:    Literal['T', 'A']
    Mjob:       Literal['at_home', 'health', 'other', 'services', 'teacher']
    Fjob:       Literal['at_home', 'health', 'other', 'services', 'teacher']
    reason:     Literal['course', 'home', 'other', 'reputation']
    guardian:   Literal['mother', 'father', 'other']
    schoolsup:  Literal['yes', 'no']
    famsup:     Literal['yes', 'no']
    paid:       Literal['yes', 'no']
    activities: Literal['yes', 'no']
    nursery:    Literal['yes', 'no']
    higher:     Literal['yes', 'no']
    internet:   Literal['yes', 'no']
    romantic:   Literal['yes', 'no']

# ── Santé ─────────────────────────────────────────────────────────────────────
@app.get("/")
def health():
    return {"status": "ok", "model_loaded": MODEL is not None}

# ── Prédiction ────────────────────────────────────────────────────────────────
@app.post("/predict")
def predict(data: StudentData):
    if MODEL is None:
        return {"error": "Modèle non chargé"}

    # 1. DataFrame brut dans le même ordre que X_raw
    etudiant = pd.DataFrame([data.model_dump()])

    # 2. LabelEncoder uniquement sur les colonnes catégorielles
    for col, le in LABEL_ENCODERS.items():
        try:
            etudiant[col] = le.transform(etudiant[col])
        except ValueError:
            # Valeur inconnue → valeur médiane (0 pour binaires)
            etudiant[col] = 0

    # 3. Réordonner les colonnes exactement comme à l'entraînement
    etudiant = etudiant[FEATURE_ORDER]

    # 4. Prédiction clampée entre 1 et 5
    raw   = MODEL.predict(etudiant)[0]
    score = int(round(max(1.0, min(5.0, float(raw)))))

    return {"score": score}