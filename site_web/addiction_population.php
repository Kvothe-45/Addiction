<?php
/* ══════════════════════════════════════════
   social_addiction.php
   Gère aussi l'appel AJAX de prédiction
   ══════════════════════════════════════════ */

// ── AJAX : prédiction ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'predict') {
    header('Content-Type: application/json');

    $fields = [
        'age'                      => FILTER_VALIDATE_INT,
        'gender'                   => FILTER_SANITIZE_SPECIAL_CHARS,
        'education_level'          => FILTER_SANITIZE_SPECIAL_CHARS,
        'employment_status'        => FILTER_SANITIZE_SPECIAL_CHARS,
        'annual_income_usd'        => FILTER_VALIDATE_INT,
        'drinks_per_week'          => FILTER_VALIDATE_INT,
        'age_started_smoking'      => FILTER_VALIDATE_INT,
        'attempts_to_quit_smoking' => FILTER_VALIDATE_INT,
        'mental_health_status'     => FILTER_SANITIZE_SPECIAL_CHARS,
        'exercise_frequency'       => FILTER_SANITIZE_SPECIAL_CHARS,
        'diet_quality'             => FILTER_SANITIZE_SPECIAL_CHARS,
        'sleep_hours'              => FILTER_VALIDATE_FLOAT,
        'social_support'           => FILTER_SANITIZE_SPECIAL_CHARS,
        'addict_smoke'             => FILTER_VALIDATE_INT,
    ];

    $data = [];
    foreach ($fields as $k => $filter) {
        $val = filter_input(INPUT_POST, $k, $filter);
        if ($val === false || $val === null) {
            echo json_encode(['error' => "Champ invalide : $k"]);
            exit;
        }
        $data[$k] = $val;
    }

    // ── Appel à l'API Render ──
    $api_url = 'https://addiction-api.onrender.com/predict/smoke';

    $payload = json_encode([
    'age'                      => (int)$data['age'],
    'gender'                   => $data['gender'],
    'education_level'          => $data['education_level'],
    'employment_status'        => $data['employment_status'],
    'annual_income_usd'        => (int)$data['annual_income_usd'],
    'drinks_per_week'          => (int)$data['drinks_per_week'],
    'age_started_smoking'      => (int)$data['age_started_smoking'],
    'attempts_to_quit_smoking' => (int)$data['attempts_to_quit_smoking'],
    'mental_health_status'     => $data['mental_health_status'],
    'exercise_frequency'       => $data['exercise_frequency'],
    'diet_quality'             => $data['diet_quality'],
    'sleep_hours'              => (float)$data['sleep_hours'],
    'social_support'           => $data['social_support'],
    'addict_smoke'             => (int)$data['addict_smoke'],
    ]);

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT,        30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        echo json_encode(['error' => 'Impossible de joindre l\'API : ' . $curl_err]);
        exit;
    }

    $result = json_decode($response, true);
    if (!$result) {
        echo json_encode(['error' => 'Réponse invalide de l\'API', 'raw' => $response]);
    } elseif (isset($result['erreur'])) {
        echo json_encode(['error' => $result['erreur']]);
    } else {
        echo json_encode($result);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Addiction Population Data — AddictData</title>
    <link rel="stylesheet" href="styles/style_general.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="styles/style_jdd.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="noise"></div>

    <!-- ── NAVBAR ── -->
    <nav class="navbar">
        <div class="nav-inner">
            <a href="index.php" class="nav-logo">
                <span class="logo-text">AddictData</span>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li class="nav-dropdown">
                    <a href="#">Datasets ▾</a>
                    <ul class="nav-dropdown-menu">
                        <li><a href="social_addiction.php" class="active">Social Addiction</a></li>
                        <li><a href="addiction_population.php">Smoke Addiction</a></li>
                        <li><a href="mobile_addiction.php">Mobile Addiction</a></li>
                        <li><a href="student-mat.php">Alcool Addiction</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <!-- ── HERO ── -->
    <header class="dataset-hero">
        <div class="hero-grid-lines" aria-hidden="true">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="grid-line"></div>
            <?php endfor; ?>
        </div>

        <div class="dataset-hero-inner">
            <div class="dataset-breadcrumb">
                <a href="index.php">Accueil</a>
                <span class="breadcrumb-sep">/</span>
                <a href="index.php#datasets">Jeux de données</a>
                <span class="breadcrumb-sep">/</span>
                <span>Addiction Population</span>
            </div>

            <div class="dataset-meta-row">
                <span class="dataset-icon-badge">🚬</span>
                <span class="dataset-tag-id">04 · Smoke Addiction</span>
            </div>

            <h1 class="dataset-hero-title">
                Addiction<br><em>population data</em>
            </h1>

            <p class="dataset-hero-sub">
                Etude de la population générale portant sur les comportements addictifs liés au tabac, analysés à travers des variables socio-démographiques, économiques et comportementales.
                <BR>Besoin d'aide ? Cliquez <a href="https://www.tabac-info-service.fr/je-me-fais-accompagner/le-39-89">ICI</a> ou appelez le 39 89, du lundi au samedi de 8h à 20h.
            </p>

            <div class="dataset-hero-pills">
                <span class="hero-pill"><span class="hero-pill-dot"></span>3 000 entrées</span>
                <span class="hero-pill"><span class="hero-pill-dot"></span>27 variables</span>
                <span class="hero-pill"><span class="hero-pill-dot"></span>Random Forest</span>
                <span class="hero-pill"><span class="hero-pill-dot"></span>Données qualitatives & quantitatives</span>
            </div>
        </div>
    </header>

<!-- ── MAIN ── -->
<main>
<div class="page-content">

    <!-- Retour -->
    <a href="index.php" class="back-link reveal">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M19 12H5M12 19l-7-7 7-7"/>
        </svg>
        Retour aux jeux de données
    </a>

    <!-- Stats bar -->
    <div class="stats-bar reveal">
        <div class="stat-box"><span class="stat-box-num">3000</span><span class="stat-box-label">Personnes</span></div>
        <div class="stat-box"><span class="stat-box-num">27</span><span class="stat-box-label">Variables</span></div>
        <div class="stat-box"><span class="stat-box-num">0.6301</span><span class="stat-box-label">R² meilleur modèle</span></div>
        <div class="stat-box"><span class="stat-box-num">4</span><span class="stat-box-label">Modèles testés</span></div>
    </div>

    <!-- ── 1. PRÉSENTATION DU DATASET ── -->
    <div class="content-block reveal" id="presentation">
        <div class="block-label"><span class="block-label-line"></span>Présentation du dataset</div>
        <h2 class="block-title">Addiction Population Data</h2>
        <p class="block-text">
            Ce jeu de données recense <strong>3000 personnes</strong> âgés entre <strong>15 et 79 ans</strong>, issues de pays du monde entier. Il couvre <strong>27 variables</strong> décrivant le profils socio-démographique, les habitudes de vie et les comportements addictifs liés au tabac. 
        </p>
        <p class="block-text">
            La <strong>variable cible</strong> est <code>Smokes_per_day</code>, qui représente le nombre de cigarettes consommées par jour et constitue l'indicateur principal analysé par nos modèles.
        </p>
        <div class="info-grid">
            <div class="info-card">
                <p class="info-card-title">Variables démographiques</p>
                <p class="info-card-text">nom, age, genre, pays, ville, statut marital, nombre d'enfants.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variables socio-économiques</p>
                <p class="info-card-text">niveau d'éducation, statut professionnel, revenus annuels.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variables comportementales & santé</p>
                <p class="info-card-text">bmi, temps de sommeil, fréquence d'exercice, qualité de l'alimentation, état de santé mentale, niveau de soutient social, présence de problèmes de santé.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variables liées à l'addiction</p>
                <p class="info-card-text">nombre de cigarettes par jour, nombre de verres d'alcool par semaine, âge de début de consommation de tabac, âge de début de consommation d'alcool, nombre de tentative d'arrêt du tabac, nombre de tentative d'arrêt de l'alcool, dépendance au tabac, dépendance à l'alcool.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variable cible</p>
                <p class="info-card-text">Nombre de cigarettes fumées par jour (2–21) constitue un problèe de <strong>régression</strong>, la variable cible étant une valeur numétique continue.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Origine des données</p>
                <p class="info-card-text">Dataset généré aléatoirement à l'aide de python à partir de distribution statistiques.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Preprocessing</p>
                <p class="info-card-text">Variables catégorielles encodées par LabelEncoder et traitement des données avec OneHotEncoder et la méthode d'ACP. Split 80/20 (2400 train / 600 test).</p>
            </div>
        </div>
    </div>

    <!-- ── 2. ANALYSES DESCRIPTIVES ── -->
    <div class="content-block reveal" id="analyses">
        <div class="analysis-section">
            <div class="block-label"><span class="block-label-line"></span>Analyses descriptives</div>
            <h2 class="block-title">Exploration des données</h2>
            <p class="analysis-intro">
                Avant de construire les modèles, nous avons analysé la distribution des consommation de tabac,
                les corrélations entre variables et les profils types des individus.
            </p>

            <div class="charts-grid">

                <!-- Graphique 1 : Distribution du nombre de cigarettes fumées par jour -->
                <div class="chart-card">
                    <p class="chart-title">Distribution du nombre de cigarettes fumées par jour</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartDistribution"></canvas>
                    </div>
                </div>

                <!-- Graphique 2 : Consommation de cigarettes moyenne par genre -->
                <div class="chart-card">
                    <p class="chart-title">Consommation moyenne de cigarettes par genre</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartGender"></canvas>
                    </div>
                </div>

                <!-- Graphique 3 : Tranche de consommation de cigarettes -->
                <div class="chart-card">
                    <p class="chart-title">Tranche de consommation de cigarettes</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartConsommation"></canvas>
                    </div>
                </div>

                <!-- Graphique 4 : Consommation moyenne de cigarettespar niveau académique -->
                <div class="chart-card">
                    <p class="chart-title">Consommation moyenne de cigarettes par niveau académique</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartNiveau"></canvas>
                    </div>
                </div>

                <!-- Graphique 5 : Scatter plot âges vs consommation de cigarettes par jour -->
                <div class="chart-card chart-card-full">
                    <p class="chart-title">Scatter plot : âges & consommation de cigarettes par jour</p>
                    <div class="chart-canvas-wrap-lg">
                        <canvas id="chartScatter"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ── 3. MODÈLES IA ── -->
    <div class="content-block reveal" id="modele">
        <div class="block-label"><span class="block-label-line"></span>Modèles d'IA</div>
        <h2 class="block-title">Comparaison des modèles entraînés</h2>
        <p class="block-text">
            Nous avons testé <strong>4 algorithmes</strong> avec différentes stratégies
            d'encodage (LabelEncoder, OneHotEncoder, ACP). Le meilleur modèle retenu est le
            <strong>Random Forest (OHE)</strong> avec un R² de 0.6301.
        </p>

        <div class="models-grid">
            <!-- Random Forest OHE — meilleur -->
            <div class="model-card best">
                <p class="model-name">Random Forest</p>
                <div class="model-metrics">
                    <div class="metric-row">
                        <span class="metric-label">R²</span>
                        <span class="metric-value good">0.6301</span>
                    </div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:63.01%"></div></div>
                    <div class="metric-row">
                        <span class="metric-label">MAE</span>
                        <span class="metric-value">1.4810</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">RMSE</span>
                        <span class="metric-value">1.9283</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">Encodage</span>
                        <span class="metric-value">OHE</span>
                    </div>
                </div>
            </div>

            <!-- XGBoost OHE -->
            <div class="model-card">
                <p class="model-name">XGBoost</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.6248</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:62.48%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">1.4890</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">1.9420</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">OHE</span></div>
                </div>
            </div>

            <!-- Naives Bayes LE -->
            <div class="model-card">
                <p class="model-name">Naive Bayes</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.6041</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:60.41%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">1.5275</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">1.9949</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">LE</span></div>
                </div>
            </div>

            <!-- KNN LE -->
            <div class="model-card">
                <p class="model-name">KNN</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.4884</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:48.84%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">1.6751</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">2.2676</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">LE</span></div>
                </div>
            </div>

            <!-- Fonctionnement -->
            <div class="info-card" style="border-left: none;">
                <p class="info-card-title">Pourquoi Random Forest ?</p>
                <p class="info-card-text">
                    Le Random Forest construit <strong>plusieurs arbres de décision</strong> sur des
                    sous-ensembles aléatoires des données, puis moyenne leurs prédictions.
                    Il est robuste au surapprentissage et capture bien les interactions non-linéaires
                    entre variables — idéal pour ce type de prédiction.
                </p>
            </div>
        </div>
    </div>

    <!-- ── 4. PRÉDICTION ── -->
    <div class="content-block reveal" id="prediction">
        <div class="block-label"><span class="block-label-line"></span>Prédiction personnalisée</div>
        <h2 class="block-title">Évalue ton nombre de cigarettes par jour</h2>
        <p class="block-text">
            Renseigne ton profil ci-dessous. Le modèle <strong>Random Forest (R² = 0.6301)</strong>
            prédit ton nombre de cigarettes consommer par jour et t'indique ta position dans trois classes : Non-fumeur ou très occasionnel (0-4), fumeur modérer (5-14), fumeur intensif (15+).
        </p>

        <div class="prediction-section">
            <form id="predictionForm">
                <div class="prediction-grid">

                    <div class="form-group">
                        <label class="form-label" for="age">Age</label>
                        <input class="form-input" type="number" id="age" name="age"
                               min="15" max="79" value="20" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gender">Genre</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="Male">Homme</option>
                            <option value="Female" selected>Femme</option>
                            <option value="Non-binary">Non-binaire</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="education_level">Niveau académique</label>
                        <select class="form-select" id="education_level" name="education_level">
                            <option value="Primary">Primaire</option>
                            <option value="Secondary">Secondaire</option>
                            <option value="High School">Lycée</option>
                            <option value="College">BTS / DUT</option>
                            <option value="University" selected>Licence</option>
                            <option value="Postgraduate">Master / Doctorat</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="employment_status">Statut professionnel</label>
                        <select class="form-select" id="employment_status" name="employment_status">
                            <option value="Student" selected>Étudiant</option>
                            <option value="Employed">Employé</option>
                            <option value="Self-Employed">Auto-entrepreneur</option>
                            <option value="Unemployed">Sans emploi</option>
                            <option value="Retired">Retraité</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="annual_income_usd">Revenus annuels (USD)</label>
                        <input class="form-input" type="number" id="annual_income_usd" name="annual_income_usd"
                            min="0" max="200000" step="1000" value="30000" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="drinks_per_week">Verres d'alcool par semaine</label>
                        <input class="form-input" type="number" id="drinks_per_week" name="drinks_per_week"
                            min="0" max="14" value="3" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="age_started_smoking">Âge de début de tabac</label>
                        <input class="form-input" type="number" id="age_started_smoking" name="age_started_smoking"
                            min="5" max="79" value="18" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="attempts_to_quit_smoking">Tentatives d'arrêt du tabac</label>
                        <input class="form-input" type="number" id="attempts_to_quit_smoking" name="attempts_to_quit_smoking"
                             min="0" max="20" value="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mental_health_status">Santé mentale</label>
                        <select class="form-select" id="mental_health_status" name="mental_health_status">
                            <option value="Good" selected>Bonne</option>
                            <option value="Average">Moyenne</option>
                            <option value="Poor">Mauvaise</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="exercise_frequency">Fréquence d'exercice</label>
                        <select class="form-select" id="exercise_frequency" name="exercise_frequency">
                            <option value="Never" >Jamais</option>
                            <option value="Rarely">Rarement</option>
                            <option value="Weekly">Chaque semaine</option>
                            <option value="Daily" selected>Tous les jours</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="diet_quality">Qualité de l'alimentation</label>
                        <select class="form-select" id="diet_quality" name="diet_quality">
                            <option value="Poor" selected>Mauvaise</option>
                            <option value="Average">Moyenne</option>
                            <option value="Good">Bonne</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sleep_hours">Heures de sommeil / nuit</label>
                        <input class="form-input" type="number" id="sleep_hours" name="sleep_hours"
                            min="3" max="12" step="0.5" value="7" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="social_support">Soutien social</label>
                        <select class="form-select" id="social_support" name="social_support">
                            <option value="Weak">Faible</option>
                            <option value="Moderate">Modéré</option>
                            <option value="Strong">Fort</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Pensez-vous être dépendant au tabac ?</label>
                        <select class="form-select" id="addict_smoke" name="addict_smoke">
                            <option value="0">Non</option>
                            <option value="1">Oui</option>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn-predict">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                    Calculer mon score
                </button>
            </form>

            <!-- Résultat -->
            <div class="prediction-result" id="predictionResult">
                <div class="result-score" id="resultScore">—</div>
                <div class="result-label">Nombre de cigarettes prédit </div>
                <div class="result-level" id="resultLevel">—</div>
                <div class="result-confidence" id="resultConfidence"></div>
                <div class="result-bar-wrap">
                    <div class="result-bar" id="resultBar"></div>
                </div>

                <!-- Positionnement dans la distribution -->
                <div class="result-distribution-wrap">
                    <p class="chart-title">Ton score dans la distribution générale</p>
                    <div class="result-dist-canvas">
                        <canvas id="chartResultPosition"></canvas>
                    </div>
                </div>

                <!-- Conseil professionnel si nombre élevé -->
                <div class="pro-advice" id="proAdvice">
                    <strong> Consommation élevée détectée ! </strong>
                    </br>Votre niveau de consommation de tabac est significativement élevé. Il peut être utile d'en parler à un professionnel de santé (médecin généraliste, tabacologue ou pharmacien). Des ressources comme <em>Tabac Info Service</em> (3989) propose un accompagnement gratuit et confidentiel pour vous aider à réduire ou arrêter de fumer. N'hésitez pas à les contacter pour bénéficier de conseils personnalisés et de soutient dans votre démarche.
                </div>
            </div>

            <!-- Message d'erreur -->
            <div id="predictionError" style="display:none; margin-top:1rem; padding:1rem; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.2); border-radius:8px; font-family:var(--font-mono); font-size:0.8rem; color:#dc2626;"></div>
        </div>
    </div>

</div>
</main>

<!-- ── FOOTER ── -->
<footer class="footer">
    <div class="footer-inner">
        <p class="footer-logo">AddictData</p>
        <p class="footer-copy">
            Projet universitaire — Science des données 4  &nbsp;·&nbsp; 2025–2026 &nbsp;·&nbsp; L3 MIASHS Université Paul Valéry Montpellier &nbsp;·&nbsp;
            Données à usage strictement pédagogique.
        </p>
    </div>
</footer>

<script>
// ── Charts ──────────────────────────────────────────
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: 'DM Mono', size: 11 }, color: '#888' } },
        y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { family: 'DM Mono', size: 11 }, color: '#888' } }
    }
};

// 1 — Distribution du nombre de cigarettes fumées par jour (histogramme)
new Chart(document.getElementById('chartDistribution'), {
    type: 'bar',
    data: {
        labels: ['2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21'],
        datasets: [{
            data: [6, 23, 54, 105, 184, 243, 352, 409, 357, 361, 290, 203, 178, 94, 59, 33, 34, 5, 6, 4],
            backgroundColor: 'rgba(201, 66, 66, 0.75)',
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(45,47,61,1)',
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} individus` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, title: { display: true, text: 'Nb d\'individus', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } },
            x: { ...chartDefaults.scales.x, title: { display: true, text: 'Nb de cigarettes fumées par jour', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } }
        }
    }
});

// 2 — moyenne de cigarette fumée par jour par genre
new Chart(document.getElementById('chartGender'), {
    type: 'bar',
    data: {
        labels: ['Female', 'Male', 'Other'],
        datasets: [{
            data: [10.18, 10.01, 9.90],
            backgroundColor: [
                'rgba(201, 66, 66, 0.75)','rgba(209, 221, 43, 0.7)','rgba(167,139,250,0.7)'],
            borderRadius: 6,
        }]
    },
    options: {
        ...chartDefaults,
        indexAxis: 'y',
        scales: {
            x: { ...chartDefaults.scales.x, min: 0, max: 9, title: { display: true, text: 'NB de cigarettes moyennes', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } },
            y: { ...chartDefaults.scales.y }
        }
    }
});

// 3 —Tranche de consommation
new Chart(document.getElementById('chartConsommation'), {
    type: 'bar',
    data: {
        labels: ['Faible','Moyen','Élevé'],
        datasets: [{
            label: 'consommation moyenne',
            data: [188,1545,1267],
            backgroundColor: ['rgba(201, 66, 66, 0.75)','rgba(209, 221, 43, 0.7)','rgba(167,139,250,0.7)'],
            borderRadius: 8,
            barPercentage: 0.5,
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` NB de personnes: ${ctx.parsed.y}` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, min: 0, max: 9 }
        }
    }
});

// 4 — Moyenne de consommation par niveau académique
new Chart(document.getElementById('chartNiveau'), {
    type: 'bar',
    data: {
        labels: ['College', 'High School', 'Postgraduate', 'Primary', 'Secondary', 'University'],
        datasets: [{
            data: [10.09, 10.18, 9.91, 10.15, 9.86, 9.91],
            backgroundColor: 'rgba(201, 66, 66, 0.75)',
            borderRadius: 6,
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} cig / j` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, title: { display: true, text: 'cigarettes / jour', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } }
        }
    }
});

// 5 — Corrélations : santé mentale & performance académique
// Données : score moyen d'addiction par tranche de mental_health (1-10)
// et par impact sur les études (Oui/Non)
new Chart(document.getElementById('chartScatter'), {
    type: 'scatter',
    data: {
        datasets : [{
            label: 'Age vs cigarettes fumées/jour',
            data: [
                {x: 66, y:5},{x:29, y:11},{x:75, y:13},{x:35, y:7},{x:38, y:8},
                {x:17, y:6},{x:36, y:9},{x:67, y:8},{x:16, y:8},{x:44, y:7},
                {x:52, y:12},{x:16, y:10},{x:78, y:10},{x:74, y:10},{x:35, y:10},
                {x:47, y:10},{x:72, y:11},{x:36, y:13},{x:63, y:8},{x:73, y:9}
            ],
            backgroundColor: [
                'rgba(244,114,182,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(244,114,182,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)',
                'rgba(255,206,86,0.7)' 
            ],
            pointRadius: 6,
        }]
    },
    options: {
        ...chartDefaults,
        scales: {
            x: {
                ...chartDefaults.scales.x,
                title: { display: true, text: 'Age', font: { size: 10, family: 'DM Mono' }, color: '#aaa' }
            },
            y: {
                ...chartDefaults.scales.y,
                title: { display: true, text: 'Cigarettes/jour', font: { size: 10, family: 'DM Mono' }, color: '#aaa' }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => `Âge: ${ctx.raw.x}, Cig/jour: ${ctx.raw.y}`
                }
            }
        }
    }
});

// ── Graphique de positionnement du résultat ──────────────
let resultPositionChart = null;
const distData = [6,23,54,105,184,243,352,409,357,361,290,203,178,94,59,33,34,5,6,4];
const distLabels = ['2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21'];

function updateResultPositionChart(userScore) {
    const scoreRounded = Math.round(userScore);
    const colors = distLabels.map((l, i) => {
        const val = i + 2;
        return Math.abs(val - scoreRounded) <= 0.5
            ? 'rgba(201, 66, 66, 0.75)'
            : 'rgba(45,47,61,0.55)';
    });

    if (resultPositionChart) {
        resultPositionChart.data.datasets[0].backgroundColor = colors;
        resultPositionChart.update();
    } else {
        resultPositionChart = new Chart(document.getElementById('chartResultPosition'), {
            type: 'bar',
            data: {
                labels: distLabels,
                datasets: [{
                    data: distData,
                    backgroundColor: colors,
                    borderRadius: 5,
                }]
            },
            options: {
                ...chartDefaults,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} individus` } }
                },
                scales: {
                    x: { ...chartDefaults.scales.x },
                    y: { ...chartDefaults.scales.y, title: { display: true, text: 'Nb d\'individus', font: { size: 9, family: 'DM Mono' }, color: '#aaa' } }
                }
            }
        });
    }
}

// ── Scroll reveal ──
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); observer.unobserve(e.target); }
    });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// ── Active nav on scroll ──
const sections = document.querySelectorAll('div[id], section[id]');
const navLinks = document.querySelectorAll('.nav-links a');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(s => { if (window.scrollY >= s.offsetTop - 130) current = s.id; });
    navLinks.forEach(a => { a.classList.toggle('active', a.getAttribute('href') === '#' + current); });
});

// ── Prédiction ──
document.getElementById('predictionForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const btn = this.querySelector('.btn-predict');
    btn.textContent = 'Calcul en cours…';
    btn.disabled = true;

    document.getElementById('predictionError').style.display = 'none';
    document.getElementById('predictionResult').classList.remove('visible');

    const formData = new FormData(this);
    formData.append('action', 'predict');

    try {
        const response = await fetch('addiction_population.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.error) {
            document.getElementById('predictionError').textContent = 'Erreur : ' + data.error;
            document.getElementById('predictionError').style.display = 'block';
        } else {
            const score   = parseFloat(data.score).toFixed(2);
            const pct     = ((score - 2) / (21 - 2) * 100).toFixed(0);
            const r2 = 'R² = 0.63';
            document.getElementById('resultConfidence').textContent = `Fiabilité du modèle : ${r2}`;
            // Niveau
            let level = '', levelClass = '';
            if (score <= 5)      { level = 'Non-fumeur ou très occasionnel';  levelClass = 'low'; }
            else if (score <= 15) { level = 'Fumeur modéré';  levelClass = 'medium'; }
            else                   { level = 'Fumeur intensif';   levelClass = 'high'; }

            document.getElementById('resultScore').textContent      = score;
            document.getElementById('resultLevel').textContent      = level;
            document.getElementById('resultLevel').className        = 'result-level ' + levelClass;
            document.getElementById('resultConfidence').textContent = `Fiabilité du modèle : ${r2}% de confiance`;

            const result = document.getElementById('predictionResult');
            result.classList.add('visible');

            // Animer la barre
            setTimeout(() => {
                document.getElementById('resultBar').style.width = pct + '%';
            }, 50);

            // Graphique de positionnement
            setTimeout(() => updateResultPositionChart(parseFloat(score)), 100);

            // Conseil professionnel si score ≥ 10
            const advice = document.getElementById('proAdvice');
            advice.style.display = parseFloat(score) >= 10 ? 'block' : 'none';
        }
    } catch (err) {
        document.getElementById('predictionError').textContent = 'Impossible de joindre le serveur.';
        document.getElementById('predictionError').style.display = 'block';
    }

    btn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg> Calculer mon score`;
    btn.disabled = false;
});
</script>
</body>
</html>