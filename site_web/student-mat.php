<?php
/* ══════════════════════════════════════════
   student-mat.php
   Gère aussi l'appel AJAX de prédiction
   ══════════════════════════════════════════ */

// ── AJAX : prédiction ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'predict') {
    header('Content-Type: application/json');

    $fields = [
        'age'        => FILTER_VALIDATE_INT,
        'G1'         => FILTER_VALIDATE_INT,
        'G2'         => FILTER_VALIDATE_INT,
        'G3'         => FILTER_VALIDATE_INT,
        'freetime'   => FILTER_VALIDATE_INT,
        'goout'      => FILTER_VALIDATE_INT,
        'health'     => FILTER_VALIDATE_INT,
        'absences'   => FILTER_VALIDATE_INT,
        'studytime'  => FILTER_VALIDATE_INT,
        'Mjob'       => FILTER_SANITIZE_SPECIAL_CHARS,
        'Fjob'       => FILTER_SANITIZE_SPECIAL_CHARS,
        'reason'     => FILTER_SANITIZE_SPECIAL_CHARS,
        'activities' => FILTER_SANITIZE_SPECIAL_CHARS,
        'romantic'   => FILTER_SANITIZE_SPECIAL_CHARS,
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
    $api_url = 'https://addiction-api.onrender.com/predict/alcohol';

   $payload = json_encode([
        'age'        => (int)$data['age'],
        'G1'         => (int)$data['G1'],
        'G2'         => (int)$data['G2'],
        'G3'         => (int)$data['G3'],
        'freetime'   => (int)$data['freetime'],
        'goout'      => (int)$data['goout'],
        'health'     => (int)$data['health'],
        'absences'   => (int)$data['absences'],
        'studytime'  => (int)$data['studytime'],
        'Mjob'       => $data['Mjob'],
        'Fjob'       => $data['Fjob'],
        'reason'     => $data['reason'],
        'activities' => $data['activities'],
        'romantic'   => $data['romantic'],
    ]);

    

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT,        60);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo json_encode(['error' => 'Impossible de joindre l\'API : ' . $err]);
    } else {
        $result = json_decode($response, true);
        if (!$result) {
            echo json_encode(['error' => 'Réponse invalide', 'raw' => $response]);
        } else {
            echo json_encode($result);
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alcool Étudiants — AddictData</title>
    <link rel="stylesheet" href="styles/style_general.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Mono:wght@300;400;500&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <link rel="stylesheet" href="styles/style_jdd.css">
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
                    <li><a href="social_addiction.php">Social Addiction</a></li>
                    <li><a href="addiction_population.php">Smoke Addiction</a></li>
                    <li><a href="mobile_addiction.php">Mobile Addiction</a></li>
                    <li><a href="student-mat.php" class="active">Alcool Addiction</a></li>
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
            <span>Alcool Étudiants</span>
        </div>
        <div class="dataset-meta-row">
            <span class="dataset-icon-badge">🍺</span>
            <span class="dataset-tag-id">02 · Alcool Addiction</span>
        </div>
        <h1 class="dataset-hero-title">
            Addiction à <em>l'alcool</em><br>chez les étudiants
        </h1>
        <p class="dataset-hero-sub">
            Étude sur la consommation d'alcool quotidienne d'étudiants en mathématiques,
            croisée avec leurs résultats scolaires, leur vie sociale et leur contexte familial.
            <BR>Besoin d'aide ? Consulter ce <a href="https://www.ameli.fr/assure/sante/themes/alcool-sante/arreter-consommation-cas-dependance">site</a> ou appelez le 0 980 980 930, de 8 h à 2 h, 7 jours sur 7 (appel non surtaxé, au prix d'une communication locale depuis un poste fixe). 
        </p>
        <div class="dataset-hero-pills">
            <span class="hero-pill"><span class="hero-pill-dot"></span>395 étudiants</span>
            <span class="hero-pill"><span class="hero-pill-dot"></span>14 variables</span>
            <span class="hero-pill"><span class="hero-pill-dot"></span>Classification · Dalc 1–5</span>
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
        <div class="stat-box"><span class="stat-box-num">395</span><span class="stat-box-label">Étudiants</span></div>
        <div class="stat-box"><span class="stat-box-num">14</span><span class="stat-box-label">Variables</span></div>
        <div class="stat-box"><span class="stat-box-num">0.2840</span><span class="stat-box-label">R² du meilleur modèle</span></div>
        <div class="stat-box"><span class="stat-box-num">4</span><span class="stat-box-label">Modèles testés</span></div>
    </div>

    <!-- ── 1. PRÉSENTATION DU DATASET ── -->
    <div class="content-block reveal" id="presentation">
        <div class="block-label"><span class="block-label-line"></span>Présentation du dataset</div>
        <h2 class="block-title">Student Alcohol Consumption — Math</h2>
        <p class="block-text">
            Ce jeu de données recense <strong>395 étudiants</strong> inscrits en cours de mathématiques.
            Il documente leur contexte familial, leurs habitudes de vie,
            leurs résultats scolaires et leur consommation d'alcool.
        </p>
        <p class="block-text">
            La <strong>variable cible</strong> est <code>Dalc</code> — la consommation d'alcool journalière,
            sur une échelle de <strong>1 à 5 verres</strong>. Il s'agit d'un problème
            de <strong>classification multiclasse</strong>.
        </p>
        <div class="info-grid">
            <div class="info-card">
                <p class="info-card-title">Variables clés</p>
                <p class="info-card-text">Notes trimestrielles (G1, G2, G3), temps libre, fréquence de sorties, santé, absences, temps d'étude, métier des parents, raison de choix d'école, activités extra-scolaires, relation amoureuse.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variable cible</p>
                <p class="info-card-text">Dalc : consommation d'alcool journalière (1–5). Il s'agit d'un problème de <strong>classification</strong> : on prédit une catégorie ordinale, non une valeur continue.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Origine des données</p>
                <p class="info-card-text">Dataset public issu de Kaggle (P. Cortez & A. Silva, 2008), dont la provenance est inconnue. Utilisé à des fins strictement pédagogiques.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Preprocessing</p>
                <p class="info-card-text">Variables catégorielles encodées par LabelEncoder et OneHotEncoder. Split 80/20 (316 train / 79 test). ACP testée en complément sur les données OHE.</p>
            </div>
        </div>
    </div>

    <!-- ── 2. ANALYSES DESCRIPTIVES ── -->
    <div class="content-block reveal" id="analyses">
        <div class="analysis-section">
            <div class="block-label"><span class="block-label-line"></span>Analyses descriptives</div>
            <h2 class="block-title">Exploration des données</h2>
            <p class="analysis-intro">
                Avant de construire les modèles, nous avons analysé la distribution de la consommation,
                les corrélations avec les notes et les profils types d'étudiants selon leur vie sociale.
            </p>

            <div class="charts-grid">

                <!-- Graphique 1 : Temps d'étude par niveau Dalc -->
                <div class="chart-card">
                    <p class="chart-title">Temps d'étude selon le niveau de consommation</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartStudytime"></canvas>
                    </div>
                </div>

                <!-- Graphique 2 : Distribution de Dalc -->
                <div class="chart-card">
                    <p class="chart-title">Raison d'inscription dans l'école</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartReason"></canvas>
                    </div>
                </div>

                <!-- Graphique 3 : Dalc selon fréquence de sorties -->
                <div class="chart-card">
                    <p class="chart-title">Nombre moyen de sortie avec des amis selon la consommation d'alcool </p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartAmisDalc"></canvas>
                    </div>
                </div>

                <!-- Graphique 4 :  -->
                <div class="chart-card">
                    <p class="chart-title">Temps libre moyen selon la consommation d'alcool</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartFreetimeD"></canvas>
                    </div>
                </div>

                <!-- Graphique 5 : Note finale G3 selon Dalc -->
                <div class="chart-card chart-card-full">
                    <p class="chart-title">Moyennes des notes selon la consommation d'alcool et les raisons d'études</p>
                    <div class="chart-canvas-wrap-lg">
                        <canvas id="chartGradesDalcReason"></canvas>
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
            d'encodage (LabelEncoder, OneHotEncoder, ACP). Cependant, notre jeu de donnée à une provenance inconnue et nos modèles ne peuvent expliquer la variance que très faiblement. De plus, le déséquilibre des classes (majorité Dalc=1)
            rend tout apprentissage à nuancer. Le meilleur modèle retenu est le
            <strong>KNN (LE)</strong> avec une R² de 0.2840.
        </p>

        <div class="models-grid">
            <!-- Random Forest — meilleur -->
            <div class="model-card best">
                <p class="model-name">KNN</p>
                <div class="model-metrics">
                    <div class="metric-row">
                        <span class="metric-label">R²</span>
                        <span class="metric-value good">0.2840</span>
                    </div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:62%"></div></div>
                    <div class="metric-row">
                        <span class="metric-label">MAE</span>
                        <span class="metric-value">0.4928</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">RMSE</span>
                        <span class="metric-value">0.7234</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">Encodage</span>
                        <span class="metric-value">LE</span>
                    </div>
                </div>
            </div>

            <!-- XGBoost -->
            <div class="model-card">
                <p class="model-name">XGBoost</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.2160</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:59%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.5798</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.7570</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">LE</span></div>
                </div>
            </div>

            <!-- KNN -->
            <div class="model-card">
                <p class="model-name">Random Forest</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.2599</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:57%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.5743</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.7355</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">LE</span></div>
                </div>
            </div>

            <!-- Naive Bayes -->
            <div class="model-card">
                <p class="model-name">Naive Bayes</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">-0.0763</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:53%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.5524</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.8870</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">ACP</span></div>
                </div>
            </div>

            <!-- Explication -->
            <div class="info-card" style="border-left: none;">
                <p class="info-card-title">Pourquoi KNN ?</p>
                <p class="info-card-text">
                    Nos analyses ont repérés que les meilleurs paramètres étaient les suivants : 9 voisins en distance manhattan avec l'encodage LabelEncoder.
                    L’algorithme des k plus proches voisins (KNN) est un classificateur d’apprentissage supervisé qui s’appuie sur la notion de proximité pour réaliser des classifications ou des prédictions sur le regroupement d’un point de données.                
                </p>
            </div>
        </div>
    </div>

    <!-- ── 4. PRÉDICTION ── -->
    <div class="content-block reveal" id="prediction">
        <div class="block-label"><span class="block-label-line"></span>Prédiction personnalisée</div>
        <h2 class="block-title">Évalue ton niveau de consommation</h2>
        <p class="block-text">
            Renseigne ton profil ci-dessous. Le modèle <strong>KNN</strong>
            prédit ton niveau de consommation d'alcool journalier sur une échelle de 1 à 5.
        </p>

        <div class="prediction-section">
            <form id="predictionForm">
                <div class="prediction-grid">

                    <div class="form-group">
                        <label class="form-label" for="age">Âge</label>
                        <input class="form-input" type="number" id="age" name="age"
                               min="15" max="22" value="17" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="G1">Note 1er trimestre (G1, /20)</label>
                        <input class="form-input" type="number" id="G1" name="G1"
                               min="0" max="20" value="11" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="G2">Note 2ème trimestre (G2, /20)</label>
                        <input class="form-input" type="number" id="G2" name="G2"
                               min="0" max="20" value="11" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="G3">Note finale (G3, /20)</label>
                        <input class="form-input" type="number" id="G3" name="G3"
                               min="0" max="20" value="11" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="freetime">Temps libre après l'école (1 = faible, 5 = élevé)</label>
                        <select class="form-select" id="freetime" name="freetime">
                            <option value="1">1 — Très faible</option>
                            <option value="2">2 — Faible</option>
                            <option value="3" selected>3 — Moyen</option>
                            <option value="4">4 — Élevé</option>
                            <option value="5">5 — Très élevé</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="goout">Sorties avec des amis (1 = rare, 5 = très fréquent)</label>
                        <select class="form-select" id="goout" name="goout">
                            <option value="1">1 — Très rare</option>
                            <option value="2">2 — Rare</option>
                            <option value="3" selected>3 — Moyen</option>
                            <option value="4">4 — Fréquent</option>
                            <option value="5">5 — Très fréquent</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="health">État de santé (1 = très mauvais, 5 = très bon)</label>
                        <select class="form-select" id="health" name="health">
                            <option value="1">1 — Très mauvais</option>
                            <option value="2">2 — Mauvais</option>
                            <option value="3">3 — Moyen</option>
                            <option value="4" selected>4 — Bon</option>
                            <option value="5">5 — Très bon</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="absences">Nombre d'absences scolaires</label>
                        <input class="form-input" type="number" id="absences" name="absences"
                               min="0" max="93" value="4" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="studytime">Temps d'étude hebdomadaire</label>
                        <select class="form-select" id="studytime" name="studytime">
                            <option value="1">1 — Moins de 2h</option>
                            <option value="2" selected>2 — Entre 2h et 5h</option>
                            <option value="3">3 — Entre 5h et 10h</option>
                            <option value="4">4 — Plus de 10h</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="Mjob">Métier de la mère</label>
                        <select class="form-select" id="Mjob" name="Mjob">
                            <option value="teacher">Enseignante</option>
                            <option value="health">Santé</option>
                            <option value="services">Services</option>
                            <option value="at_home">À la maison</option>
                            <option value="other" selected>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="Fjob">Métier du père</label>
                        <select class="form-select" id="Fjob" name="Fjob">
                            <option value="teacher">Enseignant</option>
                            <option value="health">Santé</option>
                            <option value="services">Services</option>
                            <option value="at_home">À la maison</option>
                            <option value="other" selected>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reason">Raison du choix de l'école</label>
                        <select class="form-select" id="reason" name="reason">
                            <option value="course" selected>Préférence pour les cours</option>
                            <option value="home">Proximité du domicile</option>
                            <option value="reputation">Réputation de l'école</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="activities">Activités extra-scolaires</label>
                        <select class="form-select" id="activities" name="activities">
                            <option value="yes" selected>Oui</option>
                            <option value="no">Non</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="romantic">En relation amoureuse</label>
                        <select class="form-select" id="romantic" name="romantic">
                            <option value="no" selected>Non</option>
                            <option value="yes">Oui</option>
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
                <div class="result-label">Niveau prédit / 5</div>
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

                <!-- Conseil si niveau élevé -->
                <div class="pro-advice" id="proAdvice">
                    <strong>⚠ Niveau de consommation élevé détecté !</strong>
                    Ton niveau de consommation d'alcool est significativement élevé. Il peut être utile d'en parler à un professionnel de santé (médecin, psychologue ou conseiller universitaire). Des ressources comme <em>Santé Psy Étudiant</em> proposent des consultations gratuites.
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
            Projet universitaire — L3 MIASHS Université Paul Valéry Montpellier &nbsp;·&nbsp; 2025–2026 &nbsp;·&nbsp;
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

// 1 — Distribution de Dalc (1–5)
new Chart(document.getElementById('chartReason'), {
    type: 'pie',
    data: {
        labels: ['Proximité (home)', 'Réputation (reputation)', 'Cours (course)', 'Autre (other)'],
        datasets: [{
            label: 'Raisons',
            // Les valeurs sont maintenant de vrais pourcentages (total = 100)
            data: [36.7, 26.6, 27.6, 9.1], 
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)'
            ],
            hoverOffset: 4 
        }]
    },
    options: {
        ...chartDefaults,
        rotation: 140, 
        plugins: {
            title: {
                display: true,
                font: { size: 14, family: 'DM Mono' },
                color: '#aaa'
            },
            legend: { 
                display: true, 
                position: 'bottom' 
            },
            tooltip: { 
                callbacks: { 
                    // On simplifie grandement le tooltip : 
                    // on affiche juste la catégorie et la valeur + "%"
                    label: function(ctx) {
                        return ` ${ctx.label} : ${ctx.parsed}%`;
                    }
                } 
            }
        },
        scales: {
            x: { display: false },
            y: { display: false }
        }
    }
});

// 2 — Consommation moyenne par genre
new Chart(document.getElementById('chartFreetimeD'), {
    // type 'line' pour tracer une droite
    type: 'line', 
    data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [{
            label: 'Temps libre moyen',
            data: [3.115942, 3.400000, 3.576923, 3.666667, 4.111111], 
            borderColor: 'rgba(54, 162, 235, 0.9)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            borderWidth: 2.5,
            pointBackgroundColor: 'rgba(54, 162, 235, 0.9)',
            pointRadius: 5,
            pointHoverRadius: 7,
            // tension: 0 permet d'avoir des droites strictes entre les points
            // tension: 0.3 ferait une courbe lissée
            tension: 0.3, 
            fill: true
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            title: {
                display: true,
                font: { size: 14, family: 'DM Mono' },
                color: '#aaa'
            },
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` Temps libre : ${ctx.parsed.y} / 5` } }
        },
        scales: {
            x: { ...chartDefaults.scales.x },
            y: { 
                ...chartDefaults.scales.y, 
                min: 3, 
                max: 4.5,
                title: { display: true, text: 'Temps libre moyen (FreeTime)', font: { size: 10, family: 'DM Mono' }, color: '#aaa' }
            }
        }
    }
});


new Chart(document.getElementById('chartAmisDalc'), {
    // type 'line' pour tracer une droite
    type: 'line', 
    data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [{
            label: 'Sorties avec amis',
            data: [2.931159, 3.360000, 3.692308, 3.666667, 4.222222], 
            borderColor: 'rgba(54, 162, 235, 0.9)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            borderWidth: 2.5,
            pointBackgroundColor: 'rgba(54, 162, 235, 0.9)',
            pointRadius: 5,
            pointHoverRadius: 7,
            // tension: 0 permet d'avoir des droites strictes entre les points
            // tension: 0.3 ferait une courbe lissée
            tension: 0.3, 
            fill: true
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            title: {
                display: true,
                font: { size: 14, family: 'DM Mono' },
                color: '#aaa'
            },
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` Sortie avec amis : ${ctx.parsed.y} / 5` } }
        },
        scales: {
            x: { ...chartDefaults.scales.x },
            y: { 
                ...chartDefaults.scales.y, 
                min: 2.5, 
                max: 4.5,
                title: { display: true, text: 'Sortie avec amis (goout)', font: { size: 10, family: 'DM Mono' }, color: '#aaa' }
            }
        }
    }
});
// 4 — Temps d'étude moyen par niveau Dalc
new Chart(document.getElementById('chartStudytime'), {
    type: 'bar',
    data: {
        labels: ['1', '2', '3', '4', '5'],
        
        datasets: [
            {
                label: '1H',
                data: [59, 24, 15, 2, 5], 
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderRadius: 4,
            },
            {
                label: '2H',
                data: [141, 38, 9, 7, 3], 
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderRadius: 4,
            },
            {
                label: '3H',
                data: [53, 11, 1, 0, 0], 
                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                borderRadius: 4,
            },
            {
                label: '4H',
                data: [23, 2, 1, 0, 1], 
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { 
                display: true,
                position: 'bottom'
            },
            tooltip: { 
                callbacks: { 
                    // Mise à jour du tooltip pour afficher un nombre entier d'étudiants
                    label: ctx => ` ${ctx.dataset.label} : ${ctx.parsed.y} étudiant(s)` 
                } 
            }
        },
        scales: {
            ...chartDefaults.scales,
            y: { 
                ...chartDefaults.scales.y, 
                // On supprime "max: 4" car l'axe Y représente maintenant un effectif (count)
                beginAtZero: true, 
                title: { 
                    display: true, 
                    text: "Nombre d'étudiants", 
                    font: { size: 10, family: 'DM Mono' }, 
                    color: '#aaa' 
                } 
            }
        }
    }
});

// 5 — Note finale (G3) selon Dalc — corrélation négative attendue
new Chart(document.getElementById('chartGradesDalcReason'), {
    type: 'bar',
    data: {
        // L'axe X : les 5 niveaux de consommation d'alcool (Dalc)
        labels: ['1', '2', '3', '4', '5'],
        
        // L'équivalent de hue="reason" : on crée 4 datasets
        datasets: [
            {
                label: 'Proximité (home)',
                data: [10.78, 10.21, 9.51, 8.83, 12.33], // Valeurs aléatoires (moyennes sur 20)
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderRadius: 4,
            },
            {
                label: 'Réputation (reputation)',
                data: [11.51, 10.42, 11.05, 0, 12.667], 
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderRadius: 4,
            },
            {
                label: 'Cours (course)',
                data: [10.52, 8.77, 11.25, 6.66, 10.77], 
                backgroundColor: 'rgba(255, 206, 86, 0.7)',
                borderRadius: 4,
            },
            {
                label: 'Autre (other)',
                data: [11.04, 11.9, 9.66, 10.66, 6.83], 
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderRadius: 4,
            }
        ]
    },
    options: {
        ...chartDefaults,
        plugins: {
            title: {
                display: true,
                font: { size: 14, family: 'DM Mono' },
                color: '#aaa'
            },
            legend: { 
                display: true,
                position: 'bottom'
            },
            tooltip: { 
                callbacks: { 
                    label: ctx => ` ${ctx.dataset.label} : ${ctx.parsed.y} / 20` 
                } 
            }
        },
        scales: {
            ...chartDefaults.scales,
            x: {
                ...chartDefaults.scales.x,
                title: { display: true, text: "Consommation d'alcool (Dalc)", font: { size: 10, family: 'DM Mono' }, color: '#aaa' }
            },
            y: { 
                ...chartDefaults.scales.y, 
                min: 0, 
                max: 20,
                title: { display: true, text: 'Note moyenne', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } 
            }
        }
    }
});

// ── Graphique de positionnement du résultat ──────────────
let resultPositionChart = null;
const distData   = [276, 75, 26, 9, 9];
const distLabels = ['1','2','3','4','5'];

function updateResultPositionChart(userScore) {
    const scoreRounded = Math.round(userScore);
    const colors = distLabels.map((l, i) => {
        const val = i + 1;
        return val === scoreRounded
            ? 'rgba(239,68,68,0.85)'
            : 'rgba(45,47,61,0.45)';
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
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} étudiants` } }
                },
                scales: {
                    x: { ...chartDefaults.scales.x },
                    y: { ...chartDefaults.scales.y,
                         title: { display: true, text: 'Nb étudiants', font: { size: 9, family: 'DM Mono' }, color: '#aaa' } }
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
        const response = await fetch('student-mat.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.error) {
            document.getElementById('predictionError').textContent = 'Erreur : ' + data.error;
            document.getElementById('predictionError').style.display = 'block';
        } else {
            const score = parseInt(data.score);
            const pct   = ((score - 1) / 4 * 100).toFixed(0);
            const acc   = data.fiabilite !== undefined ? data.fiabilite : '92';

            // Niveau
            let level = '', levelClass = '';
            if (score <= 1)      { level = 'Très faible';  levelClass = 'low'; }
            else if (score <= 2) { level = 'Faible';        levelClass = 'low'; }
            else if (score <= 3) { level = 'Modéré';        levelClass = 'medium'; }
            else if (score <= 4) { level = 'Élevé';         levelClass = 'high'; }
            else                 { level = 'Très élevé';    levelClass = 'high'; }

            document.getElementById('resultScore').textContent      = score;
            document.getElementById('resultLevel').textContent      = level;
            document.getElementById('resultLevel').className        = 'result-level ' + levelClass;
            document.getElementById('resultConfidence').textContent = `Fiabilité du modèle : ${acc}% de confiance`;

            const result = document.getElementById('predictionResult');
            result.classList.add('visible');

            setTimeout(() => {
                document.getElementById('resultBar').style.width = pct + '%';
            }, 50);

            setTimeout(() => updateResultPositionChart(score), 100);

            // Conseil si Dalc prédit ≥ 4
            const advice = document.getElementById('proAdvice');
            advice.style.display = score >= 4 ? 'block' : 'none';
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