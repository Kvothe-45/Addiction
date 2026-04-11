"""
app.py — Serveur FastAPI déployé sur Render.
Expose 4 endpoints POST /predict/<dataset> qui remplacent les appels shell_exec() PHP.
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import numpy as np
import pandas as pd
import joblib
import pickle
import json
import os
import sys

app = FastAPI()

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

BASE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "site_web")
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# ── Chargement des modèles au démarrage ───────────────────────────────────────

# Social
model_social         = joblib.load(os.path.join(BASE, "model.pkl"))
encoders_social      = joblib.load(os.path.join(BASE, "encoders.pkl"))
feature_names_social = joblib.load(os.path.join(BASE, "feature_names.pkl"))
CATEGORICAL_COLS_SOCIAL = [
    'Gender', 'Academic_Level', 'Country', 'Most_Used_Platform',
    'Affects_Academic_Performance', 'Relationship_Status'
]

# Alcohol
pipeline_alcohol = joblib.load(os.path.join(BASE, "model_alcohol.pkl"))

# Mobile
with open(os.path.join(BASE, "model_mobile_rf.pkl"), "rb") as f:
    model_mobile = pickle.load(f)
with open(os.path.join(BASE, "model_mobile_columns.json"), "r") as f:
    feature_names_mobile = json.load(f)
with open(os.path.join(BASE, "model_mobile_encoders.pkl"), "rb") as f:
    label_encoders_mobile = pickle.load(f)

# Smoke
model_smoke         = joblib.load(os.path.join(BASE, "model_smoke.pkl"))
feature_names_smoke = joblib.load(os.path.join(BASE, "feature_names_smoke.pkl"))
from models.encoder import oneHotEncoder


# ── Schémas Pydantic ──────────────────────────────────────────────────────────

class SocialInput(BaseModel):
    Age: float
    Gender: str
    Academic_Level: str
    Country: str
    Avg_Daily_Usage_Hours: float
    Most_Used_Platform: str
    Affects_Academic_Performance: str
    Sleep_Hours_Per_Night: float
    Mental_Health_Score: float
    Relationship_Status: str
    Conflicts_Over_Social_Media: float

class AlcoholInput(BaseModel):
    age: int
    G1: int
    G2: int
    G3: int
    freetime: int
    goout: int
    health: int
    absences: int
    studytime: int
    Mjob: str
    Fjob: str
    reason: str
    activities: str
    romantic: str

class MobileInput(BaseModel):
    age: int
    gender: str
    occupation: str
    education: str
    screen_time: float
    unlocks: int
    social_hours: float
    sleep_hours: float
    mental_health: int
    stress: int
    first_phone: int
    has_app: str
    physical: float

class SmokeInput(BaseModel):
    age: int
    gender: str
    education_level: str
    employment_status: str
    annual_income_usd: int
    drinks_per_week: int
    age_started_smoking: int
    attempts_to_quit_smoking: int
    mental_health_status: str
    exercise_frequency: str
    diet_quality: str
    sleep_hours: float
    social_support: str
    addict_smoke: int


# ── Endpoints ─────────────────────────────────────────────────────────────────

@app.get("/")
def index():
    return {"status": "ok", "message": "API Addiction opérationnelle"}


@app.post("/predict/social")
def predict_social(data: SocialInput):
    try:
        row = data.dict()
        for col in CATEGORICAL_COLS_SOCIAL:
            le  = encoders_social[col]
            val = str(row[col])
            row[col] = int(le.transform([val])[0]) if val in le.classes_ else 0
        for col in feature_names_social:
            if col not in CATEGORICAL_COLS_SOCIAL:
                row[col] = float(row[col])

        X     = np.array([[row[col] for col in feature_names_social]])
        score = round(float(model_social.predict(X)[0]), 1)
        score = max(2.0, min(9.0, score))

        from sklearn.ensemble import RandomForestRegressor
        if isinstance(model_social, RandomForestRegressor):
            tree_preds = np.array([t.predict(X)[0] for t in model_social.estimators_])
            std = float(np.std(tree_preds))
        else:
            std = 0.5

        fiabilite = min(100, int(round(max(0.0, 100.0 - std * 20.0))))
        return {"score": score, "fiabilite": fiabilite}
    except Exception as e:
        return {"erreur": str(e)}


@app.post("/predict/alcohol")
def predict_alcohol(data: AlcoholInput):
    try:
        etudiant  = pd.DataFrame([data.dict()])
        score     = int(pipeline_alcohol.predict(etudiant)[0])
        probas    = pipeline_alcohol.predict_proba(etudiant)[0]
        fiabilite = round(float(max(probas)) * 100, 1)
        return {"score": score, "fiabilite": fiabilite}
    except Exception as e:
        return {"erreur": str(e)}


@app.post("/predict/mobile")
def predict_mobile(data: MobileInput):
    try:
        cols_cat = [
            'Country', 'Gender', 'Occupation', 'Education_Level',
            'Relationship_Status', 'Has_Children', 'Urban_or_Rural',
            'Internet_Connection_Type', 'Primary_Device_Brand',
            'Has_Screen_Time_Management_App', 'Has_Night_Mode_On'
        ]
        profil_raw = {
            'Age': data.age, 'Gender': data.gender, 'Occupation': data.occupation,
            'Education_Level': data.education, 'Daily_Screen_Time_Hours': data.screen_time,
            'Phone_Unlocks_Per_Day': data.unlocks, 'Social_Media_Usage_Hours': data.social_hours,
            'Sleep_Hours': data.sleep_hours, 'Mental_Health_Score': data.mental_health,
            'Stress_Level': data.stress, 'Age_First_Phone': data.first_phone,
            'Has_Screen_Time_Management_App': data.has_app, 'Physical_Activity_Hours': data.physical,
            'Country': 'USA', 'Income_USD': 15000, 'Gaming_Usage_Hours': 1.0,
            'Streaming_Usage_Hours': 1.0, 'Messaging_Usage_Hours': 1.0,
            'Work_Related_Usage_Hours': 0.0, 'Depression_Score': 10, 'Anxiety_Score': 10,
            'Relationship_Status': 'Single', 'Has_Children': 'No', 'Urban_or_Rural': 'Urban',
            'Time_Spent_With_Family_Hours': 1.0, 'Online_Shopping_Hours': 0.5,
            'Internet_Connection_Type': 'WiFi', 'Primary_Device_Brand': 'Apple',
            'Monthly_Data_Usage_GB': 10.0, 'Has_Night_Mode_On': 'No',
            'Push_Notifications_Per_Day': 30, 'Tech_Savviness_Score': 5.0, 'Addiction_screen': 1.0,
        }

        profil_enc = {}
        for col, val in profil_raw.items():
            if col in cols_cat and col in label_encoders_mobile:
                try:
                    profil_enc[col] = int(label_encoders_mobile[col].transform([val])[0])
                except ValueError:
                    profil_enc[col] = 0
            else:
                profil_enc[col] = val

        X_input = pd.DataFrame([profil_enc])[feature_names_mobile]
        pred    = max(0, min(3, int(round(float(model_mobile.predict(X_input)[0])))))
        level   = {0: 'Low', 1: 'Moderate', 2: 'High', 3: 'Severe'}[pred]
        score   = round(pred / 3 * 10, 1)

        factors = []
        if data.screen_time   > 7:     factors.append("Temps d'écran excessif")
        if data.social_hours  > 5:     factors.append("Usage réseaux sociaux élevé")
        if data.unlocks       > 80:    factors.append("Comportement compulsif (déverrouillages)")
        if data.sleep_hours   < 6:     factors.append("Manque de sommeil")
        if data.stress        > 20:    factors.append("Stress élevé")
        if data.first_phone   < 12:    factors.append("Exposition précoce au mobile")
        if data.has_app       == 'No': factors.append("Absence d'app de contrôle")
        if data.physical      < 0.5:   factors.append("Sédentarité")
        if data.mental_health < 8:     factors.append("Santé mentale fragilisée")
        if not factors:                factors.append("Profil équilibré")

        return {"score": score, "level": level, "fiabilite": 91, "factors": factors}
    except Exception as e:
        return {"erreur": str(e)}


@app.post("/predict/smoke")
def predict_smoke(data: SmokeInput):
    try:
        DEFAULTS = {
            'id': 0, 'name': 'Unknown', 'country': 'France', 'city': 'Unknown',
            'marital_status': 'Single', 'children_count': 0,
            'age_started_drinking': 18, 'attempts_to_quit_drinking': 0,
            'has_health_issues': False, 'bmi': 25.0,
            'therapy_history': 'None', 'addict_drink': 0,
        }
        personne     = {**DEFAULTS, **data.dict()}
        personne_df  = pd.DataFrame([personne])
        personne_ohe = oneHotEncoder(personne_df.copy())
        X            = personne_ohe.reindex(columns=feature_names_smoke, fill_value=0)
        score        = round(float(model_smoke.predict(X)[0]), 1)
        niveau       = (
            "Non-fumeur ou très occasionnel" if score < 5
            else "Fumeur modéré" if score < 15
            else "Fumeur intensif"
        )
        return {"score": score, "niveau": niveau}
    except Exception as e:
        return {"erreur": str(e)}