<?php
declare(strict_types=1);
if (PHP_VERSION_ID < 80100) exit('PHP 8.1+ required');

/** Subfolder-safe base URL helper */
function base_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $base   = rtrim($dir, '/');
    $prefix = ($base === '') ? '' : $base;
    return $scheme . '://' . $host . $prefix . '/' . ltrim($path, '/');
}
define('BASE_URL', base_url());

// Optional server-side calc (unchanged)
$serverResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unitSystem = ($_POST['unit'] ?? 'metric') === 'imperial' ? 'imperial' : 'metric';
    $heightCm = null; $weightKg = null;

    if ($unitSystem === 'metric') {
        $heightCm = filter_input(INPUT_POST, 'height_cm', FILTER_VALIDATE_FLOAT);
        $weightKg = filter_input(INPUT_POST, 'weight_kg', FILTER_VALIDATE_FLOAT);
    } else {
        $heightFt = filter_input(INPUT_POST, 'height_ft', FILTER_VALIDATE_FLOAT);
        $heightIn = filter_input(INPUT_POST, 'height_in', FILTER_VALIDATE_FLOAT);
        $weightLb = filter_input(INPUT_POST, 'weight_lb', FILTER_VALIDATE_FLOAT);
        if ($heightFt !== false && $heightIn !== false) $heightCm = (($heightFt * 12.0) + $heightIn) * 2.54;
        if ($weightLb !== false) $weightKg = $weightLb * 0.45359237;
    }

    if ($heightCm && $heightCm > 0 && $weightKg && $weightKg > 0) {
        $heightM = $heightCm / 100.0;
        $bmi = $weightKg / ($heightM * $heightM);
        $category = 'Underweight';
        if ($bmi >= 18.5 && $bmi < 25) $category = 'Normal';
        elseif ($bmi >= 25 && $bmi < 30) $category = 'Overweight';
        elseif ($bmi >= 30 && $bmi < 35) $category = 'Obesity I';
        elseif ($bmi >= 35 && $bmi < 40) $category = 'Obesity II';
        elseif ($bmi >= 40) $category = 'Obesity III';
        $serverResult = ['bmi' => round($bmi, 1), 'category' => $category];
    }
}
?>
<?php include __DIR__ . '/includes/head.partial.php'; ?>
<body class="bg-body">
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= htmlspecialchars(BASE_URL) ?>">BMI Calculator</a>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h1 class="h4 mb-3">Body Mass Index (BMI) Calculator</h1>
          <p class="text-secondary small mb-4">Enter your height and weight below. Works with metric or imperial and updates instantly.</p>

          <ul class="nav nav-pills mb-3" id="unitTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="metric-tab" data-bs-toggle="pill" data-bs-target="#metric-pane" type="button" role="tab" aria-controls="metric-pane" aria-selected="true">Metric</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="imperial-tab" data-bs-toggle="pill" data-bs-target="#imperial-pane" type="button" role="tab" aria-controls="imperial-pane" aria-selected="false">Imperial</button>
            </li>
          </ul>

          <form id="bmiForm" method="post" novalidate>
            <input type="hidden" name="unit" id="unitField" value="metric">
            <div class="tab-content" id="unitTabContent">
              <div class="tab-pane fade show active" id="metric-pane" role="tabpanel" aria-labelledby="metric-tab" tabindex="0">
                <div class="row g-3">
                  <div class="col-sm-6">
                    <label for="height_cm" class="form-label">Height (cm)</label>
                    <input type="number" step="0.1" min="50" max="300" class="form-control" id="height_cm" name="height_cm" placeholder="e.g., 175">
                  </div>
                  <div class="col-sm-6">
                    <label for="weight_kg" class="form-label">Weight (kg)</label>
                    <input type="number" step="0.1" min="10" max="500" class="form-control" id="weight_kg" name="weight_kg" placeholder="e.g., 70">
                  </div>
                </div>
              </div>

              <div class="tab-pane fade" id="imperial-pane" role="tabpanel" aria-labelledby="imperial-tab" tabindex="0">
                <div class="row g-3">
                  <div class="col-sm-4">
                    <label for="height_ft" class="form-label">Height (ft)</label>
                    <input type="number" step="1" min="1" max="8" class="form-control" id="height_ft" name="height_ft" placeholder="e.g., 5">
                  </div>
                  <div class="col-sm-4">
                    <label for="height_in" class="form-label">Height (in)</label>
                    <input type="number" step="0.1" min="0" max="11.9" class="form-control" id="height_in" name="height_in" placeholder="e.g., 9">
                  </div>
                  <div class="col-sm-4">
                    <label for="weight_lb" class="form-label">Weight (lb)</label>
                    <input type="number" step="0.1" min="20" max="1100" class="form-control" id="weight_lb" name="weight_lb" placeholder="e.g., 154">
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center gap-2 mt-4">
              <button type="submit" class="btn btn-primary">Calculate (Server)</button>
              <button type="button" id="resetBtn" class="btn btn-outline-secondary">Reset</button>
            </div>
            <p class="form-text mt-2">Live client-side updates, plus a server-side button for verification.</p>
          </form>

          <?php if ($serverResult): ?>
            <div class="alert alert-info mt-3 mb-0">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Server Result:</span>
                <span>BMI <strong><?= htmlspecialchars((string)$serverResult['bmi']) ?></strong> — <strong><?= htmlspecialchars((string)$serverResult['category']) ?></strong></span>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-3">Your Result</h2>

          <div class="result-box border rounded-3 p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div class="text-secondary small">BMI</div>
                <div id="bmiValue" class="display-6 fw-semibold">—</div>
              </div>
              <div class="text-end">
                <div class="text-secondary small">Category</div>
                <div id="bmiCategory" class="fs-5 fw-semibold">—</div>
              </div>
            </div>

            <!-- Removed the duplicate top progress bar -->
            <canvas id="bmiChart" class="mt-3" height="110" aria-label="BMI position on scale"></canvas>
          </div>

          <div class="alert alert-secondary small mb-0">
            <strong>Note:</strong> BMI is a screening tool and doesn’t account for muscle mass, ethnicity, age, or body composition.
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-4">
        <div class="card-body">
          <h3 class="h6">Categories</h3>
          <ul class="list-unstyled small mb-0">
            <li><span class="legend-dot bg-underweight me-2"></span>Underweight: &lt; 18.5</li>
            <li><span class="legend-dot bg-normal me-2"></span>Normal: 18.5 – 24.9</li>
            <li><span class="legend-dot bg-overweight me-2"></span>Overweight: 25.0 – 29.9</li>
            <li><span class="legend-dot bg-obese me-2"></span>Obesity I: 30.0 – 34.9</li>
            <li><span class="legend-dot bg-obese me-2"></span>Obesity II: 35.0 – 39.9</li>
            <li><span class="legend-dot bg-obese me-2"></span>Obesity III: ≥ 40</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/includes/footer.partial.php'; ?>
</body>
</html>