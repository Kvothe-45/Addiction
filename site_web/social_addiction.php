<?php
/* ══════════════════════════════════════════
   social_addiction.php
   Gère aussi l'appel AJAX de prédiction
   ══════════════════════════════════════════ */

// ── AJAX : prédiction ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'predict') {
    header('Content-Type: application/json');

    $fields = [
        'age'             => FILTER_VALIDATE_INT,
        'gender'          => FILTER_SANITIZE_SPECIAL_CHARS,
        'academic_level'  => FILTER_SANITIZE_SPECIAL_CHARS,
        'country'         => FILTER_SANITIZE_SPECIAL_CHARS,
        'avg_daily_hours' => FILTER_VALIDATE_FLOAT,
        'platform'        => FILTER_SANITIZE_SPECIAL_CHARS,
        'affects_perf'    => FILTER_SANITIZE_SPECIAL_CHARS,
        'sleep_hours'     => FILTER_VALIDATE_FLOAT,
        'mental_health'   => FILTER_VALIDATE_INT,
        'relationship'    => FILTER_SANITIZE_SPECIAL_CHARS,
        'conflicts'       => FILTER_VALIDATE_INT,
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

    // ── Appel à l'API Render (remplace shell_exec + predict.py) ──
    $api_url = 'https://addiction-api.onrender.com/predict/social';

    $payload = json_encode([
        'Age'                          => (float)$data['age'],
        'Gender'                       => $data['gender'],
        'Academic_Level'               => $data['academic_level'],
        'Country'                      => $data['country'],
        'Avg_Daily_Usage_Hours'        => (float)$data['avg_daily_hours'],
        'Most_Used_Platform'           => $data['platform'],
        'Affects_Academic_Performance' => $data['affects_perf'],
        'Sleep_Hours_Per_Night'        => (float)$data['sleep_hours'],
        'Mental_Health_Score'          => (float)$data['mental_health'],
        'Relationship_Status'          => $data['relationship'],
        'Conflicts_Over_Social_Media'  => (float)$data['conflicts'],
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
    <title>Réseaux Sociaux — AddictData</title>
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
            <span>Réseaux Sociaux</span>
        </div>
        <div class="dataset-meta-row">
            <span class="dataset-icon-badge">💬</span>
            <span class="dataset-tag-id">01 · Social Addiction</span>
        </div>
        <h1 class="dataset-hero-title">
            Addiction aux<br><em>réseaux sociaux</em>
        </h1>
        <p class="dataset-hero-sub">
            Analyse comportementale de l'usage excessif des plateformes numériques
            et son impact sur la santé mentale de 705 étudiants.
            <BR>Besoin d'aide ? Cliquez <a href="https://mda.loire-atlantique.fr/44/ados-et-jeunes/je-suis-accro-aux-reseaux-sociaux-et/ou-aux-jeux-video/mda_8497">ICI</a> ou appelez le 0800 200 000, du lundi au vendredi de 9h à 19h.

        </p>
        <div class="dataset-hero-pills">
            <span class="hero-pill"><span class="hero-pill-dot"></span>705 étudiants</span>
            <span class="hero-pill"><span class="hero-pill-dot"></span>11 variables</span>
            <span class="hero-pill"><span class="hero-pill-dot"></span>Régression · Score 2–9</span>
            <span class="hero-pill"><span class="hero-pill-dot"></span>R² = 0.9903</span>
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
        <div class="stat-box"><span class="stat-box-num">705</span><span class="stat-box-label">Étudiants</span></div>
        <div class="stat-box"><span class="stat-box-num">11</span><span class="stat-box-label">Variables</span></div>
        <div class="stat-box"><span class="stat-box-num">0.99</span><span class="stat-box-label">R² meilleur modèle</span></div>
        <div class="stat-box"><span class="stat-box-num">4</span><span class="stat-box-label">Modèles testés</span></div>
    </div>

    <!-- ── 1. PRÉSENTATION DU DATASET ── -->
    <div class="content-block reveal" id="presentation">
        <div class="block-label"><span class="block-label-line"></span>Présentation du dataset</div>
        <h2 class="block-title">Students Social Media Addiction</h2>
        <p class="block-text">
            Ce jeu de données recense <strong>705 étudiants</strong> issus de différents pays et niveaux
            académiques. Il capture leur comportement sur les réseaux sociaux — temps d'usage quotidien,
            plateforme préférée, impact sur les performances scolaires — ainsi que des indicateurs de
            santé mentale et de qualité de vie.
        </p>
        <p class="block-text">
            La <strong>variable cible</strong> est l'<code>Addicted_Score</code>, un score d'addiction
            allant de <strong>2 à 9</strong> qui synthétise le niveau de dépendance numérique de chaque étudiant.
        </p>
        <div class="info-grid">
            <div class="info-card">
                <p class="info-card-title">Variables clés</p>
                <p class="info-card-text">Âge, genre, niveau académique, pays, heures d'usage quotidien, plateforme principale, impact sur les études, heures de sommeil, score de santé mentale, statut relationnel, conflits liés aux réseaux.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Variable cible</p>
                <p class="info-card-text">Score d'addiction (2–9). Il s'agit d'un problème de <strong>régression</strong> : on prédit une valeur numérique continue, non une classe binaire.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Origine des données</p>
                <p class="info-card-text">Dataset public issu de Kaggle, compilé à partir d'enquêtes menées auprès d'étudiants de plusieurs universités. Utilisé à des fins strictement pédagogiques.</p>
            </div>
            <div class="info-card">
                <p class="info-card-title">Preprocessing</p>
                <p class="info-card-text">Variables catégorielles encodées par LabelEncoder et OneHotEncoder. Split 80/20 (564 train / 141 test). ACP testée en complément.</p>
            </div>
        </div>
    </div>

    <!-- ── 2. ANALYSES DESCRIPTIVES ── -->
    <div class="content-block reveal" id="analyses">
        <div class="analysis-section">
            <div class="block-label"><span class="block-label-line"></span>Analyses descriptives</div>
            <h2 class="block-title">Exploration des données</h2>
            <p class="analysis-intro">
                Avant de construire les modèles, nous avons analysé la distribution du score d'addiction,
                les corrélations entre variables et les profils types d'étudiants.
            </p>

            <div class="charts-grid">

                <!-- Graphique 1 : Distribution du score d'addiction -->
                <div class="chart-card">
                    <p class="chart-title">Distribution du score d'addiction</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartDistribution"></canvas>
                    </div>
                </div>

                <!-- Graphique 2 : Score moyen par plateforme -->
                <div class="chart-card">
                    <p class="chart-title">Score moyen par plateforme</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartPlateforme"></canvas>
                    </div>
                </div>

                <!-- Graphique 3 : Score moyen par genre -->
                <div class="chart-card">
                    <p class="chart-title">Score d'addiction selon le genre</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartGenre"></canvas>
                    </div>
                </div>

                <!-- Graphique 4 : Heures d'usage par niveau académique -->
                <div class="chart-card">
                    <p class="chart-title">Usage quotidien par niveau académique</p>
                    <div class="chart-canvas-wrap">
                        <canvas id="chartNiveau"></canvas>
                    </div>
                </div>

                <!-- Graphique 5 : Corrélation santé mentale -->
                <div class="chart-card chart-card-full">
                    <p class="chart-title">Corrélation : santé mentale & score d'addiction</p>
                    <div class="chart-canvas-wrap-lg">
                        <canvas id="chartCorrelations"></canvas>
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
            Nous avons testé <strong>5 algorithmes de régression</strong> avec différentes stratégies
            d'encodage (LabelEncoder, OneHotEncoder, ACP). Le meilleur modèle retenu est le
            <strong>Random Forest (OHE)</strong> avec un R² de 0.9903.
        </p>

        <div class="models-grid">
            <!-- Random Forest OHE — meilleur -->
            <div class="model-card best">
                <p class="model-name">Random Forest</p>
                <div class="model-metrics">
                    <div class="metric-row">
                        <span class="metric-label">R²</span>
                        <span class="metric-value good">0.9903</span>
                    </div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:99%"></div></div>
                    <div class="metric-row">
                        <span class="metric-label">MAE</span>
                        <span class="metric-value">0.0374</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">RMSE</span>
                        <span class="metric-value">0.1555</span>
                    </div>
                    <div class="metric-row">
                        <span class="metric-label">Encodage</span>
                        <span class="metric-value">OHE</span>
                    </div>
                </div>
            </div>

            <!-- KNN LE -->
            <div class="model-card">
                <p class="model-name">KNN</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.9883</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:98%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.0565</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.1710</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">LE</span></div>
                </div>
            </div>

            <!-- XGBoost OHE -->
            <div class="model-card">
                <p class="model-name">XGBoost</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.9854</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:98%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.0593</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.1913</span></div>
                    <div class="metric-row"><span class="metric-label">Encodage</span><span class="metric-value">OHE</span></div>
                </div>
            </div>

            <!-- Naive Bayes LE -->
            <div class="model-card">
                <p class="model-name">Naive Bayes</p>
                <div class="model-metrics">
                    <div class="metric-row"><span class="metric-label">R²</span><span class="metric-value">0.9576</span></div>
                    <div class="metric-bar-wrap"><div class="metric-bar" style="width:95%"></div></div>
                    <div class="metric-row"><span class="metric-label">MAE</span><span class="metric-value">0.1229</span></div>
                    <div class="metric-row"><span class="metric-label">RMSE</span><span class="metric-value">0.3257</span></div>
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
                    entre variables — idéal pour ce type de score comportemental.
                </p>
            </div>
        </div>
    </div>

    <!-- ── 4. PRÉDICTION ── -->
    <div class="content-block reveal" id="prediction">
        <div class="block-label"><span class="block-label-line"></span>Prédiction personnalisée</div>
        <h2 class="block-title">Évalue ton score d'addiction</h2>
        <p class="block-text">
            Renseigne ton profil ci-dessous. Le modèle <strong>Random Forest (R² = 0.9903)</strong>
            prédit ton score d'addiction aux réseaux sociaux sur une échelle de 2 à 9.
        </p>

        <div class="prediction-section">
            <form id="predictionForm">
                <div class="prediction-grid">

                    <div class="form-group">
                        <label class="form-label" for="age">Âge</label>
                        <input class="form-input" type="number" id="age" name="age"
                               min="15" max="35" value="20" required>
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
                        <label class="form-label" for="academic_level">Niveau académique</label>
                        <select class="form-select" id="academic_level" name="academic_level">
                            <option value="Undergraduate" selected>Licence</option>
                            <option value="Graduate">Master</option>
                            <option value="High School">Lycée</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="country">Pays</label>
                        <input class="form-input" list="country-list" id="country" name="country"
                               placeholder="Saisir un pays…" value="France" required autocomplete="off">
                        <datalist id="country-list">
                            <?php
                            $pays = ["Afghanistan","Albania","Algeria","Andorra","Angola","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Central African Republic","Chad","Chile","China","Colombia","Comoros","Congo","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominican Republic","DR Congo","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Eswatini","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Ivory Coast","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Mauritania","Mauritius","Mexico","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar","Namibia","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","North Korea","North Macedonia","Norway","Oman","Pakistan","Palestine","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russia","Rwanda","Saudi Arabia","Senegal","Serbia","Sierra Leone","Singapore","Slovakia","Slovenia","Somalia","South Africa","South Korea","South Sudan","Spain","Sri Lanka","Sudan","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Uganda","UK","Ukraine","UAE","Uruguay","USA","Uzbekistan","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"];
                            foreach ($pays as $p) echo "<option value=\"$p\">";
                            ?>
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="avg_daily_hours">Heures d'usage quotidien</label>
                        <input class="form-input" type="number" id="avg_daily_hours" name="avg_daily_hours"
                               min="0" max="24" step="0.5" value="3" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="platform">Plateforme principale</label>
                        <select class="form-select" id="platform" name="platform">
                            <option value="Instagram" selected>Instagram</option>
                            <option value="TikTok">TikTok</option>
                            <option value="YouTube">YouTube</option>
                            <option value="Twitter">Twitter / X</option>
                            <option value="Facebook">Facebook</option>
                            <option value="Snapchat">Snapchat</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="WhatsApp">WhatsApp</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="affects_perf">Impact sur les études</label>
                        <select class="form-select" id="affects_perf" name="affects_perf">
                            <option value="Yes" selected>Oui</option>
                            <option value="No">Non</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="sleep_hours">Heures de sommeil / nuit</label>
                        <input class="form-input" type="number" id="sleep_hours" name="sleep_hours"
                               min="3" max="12" step="0.5" value="7" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mental_health">Score santé mentale (1–10)</label>
                        <input class="form-input" type="number" id="mental_health" name="mental_health"
                               min="1" max="10" value="6" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="relationship">Statut relationnel</label>
                        <select class="form-select" id="relationship" name="relationship">
                            <option value="Single" selected>Célibataire</option>
                            <option value="In Relationship">En couple</option>
                            <option value="Complicated">Compliqué</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="conflicts">Conflits liés aux réseaux (nb/semaine)</label>
                        <input class="form-input" type="number" id="conflicts" name="conflicts"
                               min="0" max="10" value="1" required>
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
                <div class="result-label">Score prédit / 9</div>
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

                <!-- Conseil professionnel si score élevé -->
                <div class="pro-advice" id="proAdvice">
                    <strong> Score élevé détecté ! </strong>
                    Ton score d'addiction est significativement élevé. Il peut être utile d'en parler à un professionnel de santé mentale (médecin, psychologue ou conseiller universitaire). Des ressources comme <em>Santé Psy Étudiant</em> proposent des consultations gratuites.
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

// 1 — Distribution du score d'addiction (histogramme)
new Chart(document.getElementById('chartDistribution'), {
    type: 'bar',
    data: {
        labels: ['2','3','4','5','6','7','8','9'],
        datasets: [{
            data: [18, 42, 78, 112, 143, 138, 98, 76],
            backgroundColor: 'rgba(45,47,61,0.75)',
            borderRadius: 6,
            hoverBackgroundColor: 'rgba(45,47,61,1)',
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} étudiants` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, title: { display: true, text: 'Nb étudiants', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } }
        }
    }
});

// 2 — Score moyen par plateforme
new Chart(document.getElementById('chartPlateforme'), {
    type: 'bar',
    data: {
        labels: ['TikTok','Instagram','Snapchat','YouTube','Facebook','WhatsApp','Twitter','LinkedIn'],
        datasets: [{
            data: [6.4, 6.1, 5.8, 5.3, 5.1, 4.9, 4.6, 4.1],
            backgroundColor: [
                'rgba(239,68,68,0.75)','rgba(239,68,68,0.6)','rgba(251,146,60,0.7)',
                'rgba(251,146,60,0.55)','rgba(45,47,61,0.5)','rgba(45,47,61,0.4)',
                'rgba(45,47,61,0.3)','rgba(45,47,61,0.2)'
            ],
            borderRadius: 6,
        }]
    },
    options: {
        ...chartDefaults,
        indexAxis: 'y',
        scales: {
            x: { ...chartDefaults.scales.x, min: 0, max: 9, title: { display: true, text: 'Score moyen', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } },
            y: { ...chartDefaults.scales.y }
        }
    }
});

// 3 — Score moyen par genre
new Chart(document.getElementById('chartGenre'), {
    type: 'bar',
    data: {
        labels: ['Homme', 'Femme', 'Non-binaire'],
        datasets: [{
            label: 'Score moyen',
            data: [5.5, 5.3, 5.6],
            backgroundColor: ['rgba(96,165,250,0.7)','rgba(244,114,182,0.7)','rgba(167,139,250,0.7)'],
            borderRadius: 8,
            barPercentage: 0.5,
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` Score moyen: ${ctx.parsed.y}` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, min: 0, max: 9 }
        }
    }
});

// 4 — Heures d'usage par niveau académique
new Chart(document.getElementById('chartNiveau'), {
    type: 'bar',
    data: {
        labels: ['Lycée', 'Licence', 'Master'],
        datasets: [{
            label: 'Médiane (h/j)',
            data: [5.5, 4.8, 4.5],
            backgroundColor: 'rgba(45,47,61,0.75)',
            borderRadius: 6,
            barPercentage: 0.4,
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} h/jour` } }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, title: { display: true, text: 'Heures / jour', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } }
        }
    }
});

// 5 — Corrélations : santé mentale & performance académique
// Données : score moyen d'addiction par tranche de mental_health (1-10)
// et par impact sur les études (Oui/Non)
new Chart(document.getElementById('chartCorrelations'), {
    type: 'line',
    data: {
        labels: ['2','3','4','5','6','7','8','9'],
        datasets: [{
            label: 'Santé mentale moyenne',
            data: [9.0, 8.0, 8.0, 7.1, 6.8, 5.9, 5.2, 4.5],
            borderColor: 'rgba(239,68,68,0.85)',
            backgroundColor: 'rgba(239,68,68,0.06)',
            borderWidth: 2.5,
            pointBackgroundColor: 'rgba(239,68,68,0.85)',
            pointRadius: 5,
            pointHoverRadius: 7,
            tension: 0,
            fill: false,
        }]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` Santé mentale : ${ctx.parsed.y}` } }
        },
        scales: {
            x: { ...chartDefaults.scales.x, title: { display: true, text: "Score d'addiction", font: { size: 10, family: 'DM Mono' }, color: '#aaa' } },
            y: { ...chartDefaults.scales.y, min: 0, max: 10, title: { display: true, text: 'Santé mentale (moyenne)', font: { size: 10, family: 'DM Mono' }, color: '#aaa' } }
        }
    }
});

// ── Graphique de positionnement du résultat ──────────────
let resultPositionChart = null;
const distData = [18, 42, 78, 112, 143, 138, 98, 76];
const distLabels = ['2','3','4','5','6','7','8','9'];

function updateResultPositionChart(userScore) {
    const scoreRounded = Math.round(userScore);
    const colors = distLabels.map((l, i) => {
        const val = i + 2;
        return Math.abs(val - scoreRounded) <= 0.5
            ? 'rgba(239,68,68,0.85)'
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
                    tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} étudiants` } }
                },
                scales: {
                    x: { ...chartDefaults.scales.x },
                    y: { ...chartDefaults.scales.y, title: { display: true, text: 'Nb étudiants', font: { size: 9, family: 'DM Mono' }, color: '#aaa' } }
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
        const response = await fetch('social_addiction.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.error) {
            document.getElementById('predictionError').textContent = 'Erreur : ' + data.error;
            document.getElementById('predictionError').style.display = 'block';
        } else {
            const score   = parseFloat(data.score).toFixed(2);
            const pct     = ((score - 2) / 7 * 100).toFixed(0);
            const r2 = data.fiabilite !== undefined ? data.fiabilite : '99';

            // Niveau
            let level = '', levelClass = '';
            if (score < 4.5)      { level = 'Niveau faible';  levelClass = 'low'; }
            else if (score < 6.5) { level = 'Niveau modéré';  levelClass = 'medium'; }
            else                   { level = 'Niveau élevé';   levelClass = 'high'; }

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

            // Conseil professionnel si score ≥ 7
            const advice = document.getElementById('proAdvice');
            advice.style.display = parseFloat(score) >= 7 ? 'block' : 'none';
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