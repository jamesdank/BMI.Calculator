<?php
declare(strict_types=1);
if (PHP_VERSION_ID < 80100) exit('PHP 8.1+ required');

$devCacheBust = '?v=' . time(); // Replace with '?v=1.0.0' for production
?>
  <!-- jQuery (version pinned) -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <!-- Bootstrap Bundle (includes Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Chart.js (version pinned) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

  <!-- App JS -->
  <script>
    // Expose base URL for JS (subfolder-safe)
    window.APP_BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="<?= htmlspecialchars(BASE_URL) ?>assets/js/app.js<?= $devCacheBust ?>"></script>