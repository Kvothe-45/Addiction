
import sys, json, os, pickle
import numpy as np
import pandas as pd

BASE = os.path.dirname(os.path.abspath(__file__))

# ── Chargement des fichiers exportés depuis le notebook ──────────────────────
try:
    with open(os.path.join(BASE, 'model_mobile_rf.pkl'), 'rb') as f:
        model = pickle.load(f)
    with open(os.path.join(BASE, 'model_mobile_columns.json'), 'r') as f:
        feature_names = json.load(f)
    with open(os.path.join(BASE, 'model_mobile_encoders.pkl'), 'rb') as f:
        label_encoders = pickle.load(f)
except FileNotFoundError as e:
    print(json.dumps({"erreur": f"Fichier manquant : {str(e)}"}))
    sys.exit(1)

# ── Lecture des arguments passés par PHP ─────────────────────────────────────
if len(sys.argv) < 14:
    print(json.dumps({"erreur": f"Arguments insuffisants ({len(sys.argv)-1}/13)"}))
    sys.exit(1)

try:
    age           = int(sys.argv[1])
    gender        = sys.argv[2]
    occupation    = sys.argv[3]
    education     = sys.argv[4]
    screen_time   = float(sys.argv[5])
    unlocks       = int(sys.argv[6])
    social_hours  = float(sys.argv[7])
    sleep_hours   = float(sys.argv[8])
    mental_health = int(sys.argv[9])
    stress        = int(sys.argv[10])
    first_phone   = int(sys.argv[11])
    has_app       = sys.argv[12]
    physical      = float(sys.argv[13])
except (ValueError, IndexError) as e:
    print(json.dumps({"erreur": f"Erreur parsing arguments : {str(e)}"}))
    sys.exit(1)

# ── Profil complet (valeurs médianes pour les colonnes non saisies) ───────────
profil_raw = {
    'Age'                            : age,
    'Gender'                         : gender,
    'Occupation'                     : occupation,
    'Education_Level'                : education,
    'Daily_Screen_Time_Hours'        : screen_time,
    'Phone_Unlocks_Per_Day'          : unlocks,
    'Social_Media_Usage_Hours'       : social_hours,
    'Sleep_Hours'                    : sleep_hours,
    'Mental_Health_Score'            : mental_health,
    'Stress_Level'                   : stress,
    'Age_First_Phone'                : first_phone,
    'Has_Screen_Time_Management_App' : has_app,
    'Physical_Activity_Hours'        : physical,
    'Country'                        : 'USA',
    'Income_USD'                     : 15000,
    'Gaming_Usage_Hours'             : 1.0,
    'Streaming_Usage_Hours'          : 1.0,
    'Messaging_Usage_Hours'          : 1.0,
    'Work_Related_Usage_Hours'       : 0.0,
    'Depression_Score'               : 10,
    'Anxiety_Score'                  : 10,
    'Relationship_Status'            : 'Single',
    'Has_Children'                   : 'No',
    'Urban_or_Rural'                 : 'Urban',
    'Time_Spent_With_Family_Hours'   : 1.0,
    'Online_Shopping_Hours'          : 0.5,
    'Internet_Connection_Type'       : 'WiFi',
    'Primary_Device_Brand'           : 'Apple',
    'Monthly_Data_Usage_GB'          : 10.0,
    'Has_Night_Mode_On'              : 'No',
    'Push_Notifications_Per_Day'     : 30,
    'Tech_Savviness_Score'           : 5.0,
    'Addiction_screen'               : 1.0,
}

# ── Encodage LabelEncoder (identique au notebook) ────────────────────────────
cols_cat = [
    'Country', 'Gender', 'Occupation', 'Education_Level',
    'Relationship_Status', 'Has_Children', 'Urban_or_Rural',
    'Internet_Connection_Type', 'Primary_Device_Brand',
    'Has_Screen_Time_Management_App', 'Has_Night_Mode_On'
]

profil_enc = {}
for col, val in profil_raw.items():
    if col in cols_cat and col in label_encoders:
        try:
            profil_enc[col] = int(label_encoders[col].transform([val])[0])
        except ValueError:
            profil_enc[col] = 0
    else:
        profil_enc[col] = val

# ── DataFrame dans le bon ordre de colonnes ──────────────────────────────────
try:
    X_input = pd.DataFrame([profil_enc])[feature_names]
except KeyError as e:
    print(json.dumps({"erreur": f"Colonne manquante : {str(e)}"}))
    sys.exit(1)

# ── Prédiction ───────────────────────────────────────────────────────────────
try:
    pred_encoded = model.predict(X_input)[0]
    pred_rounded = int(round(float(pred_encoded)))
    pred_rounded = max(0, min(3, pred_rounded))  # 0=Low 1=Moderate 2=High 3=Severe
except Exception as e:
    print(json.dumps({"erreur": f"Erreur prédiction : {str(e)}"}))
    sys.exit(1)

# ── Niveau et score ──────────────────────────────────────────────────────────
level_map    = {0: 'Low', 1: 'Moderate', 2: 'High', 3: 'Severe'}
level        = level_map[pred_rounded]
score_sur_10 = round(pred_rounded / 3 * 10, 1)

# ── Facteurs contributeurs ───────────────────────────────────────────────────
factors = []
if screen_time   > 7:     factors.append("Temps d'écran excessif")
if social_hours  > 5:     factors.append("Usage réseaux sociaux élevé")
if unlocks       > 80:    factors.append("Comportement compulsif (déverrouillages)")
if sleep_hours   < 6:     factors.append("Manque de sommeil")
if stress        > 20:    factors.append("Stress élevé")
if first_phone   < 12:    factors.append("Exposition précoce au mobile")
if has_app       == 'No': factors.append("Absence d'app de contrôle")
if physical      < 0.5:   factors.append("Sédentarité")
if mental_health < 8:     factors.append("Santé mentale fragilisée")
if not factors:           factors.append("Profil équilibré")

# ── Sortie JSON ──────────────────────────────────────────────────────────────
print(json.dumps({
    "score"    : score_sur_10,
    "level"    : level,
    "fiabilite": 91,
    "factors"  : factors
}))