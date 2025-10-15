<?php
declare(strict_types=1);
if (PHP_VERSION_ID < 80100) exit('PHP 8.1+ required');

$devCacheBust = '?v=' . time(); // Replace with '?v=1.0.0' for production
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BMI Calculator</title>

  <!-- Bootstrap 5 (version pinned) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- App CSS -->
  <link href="<?= htmlspecialchars(BASE_URL) ?>assets/css/styles.css<?= $devCacheBust ?>" rel="stylesheet">

  <!-- Favicon (optional safe default) -->
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='128' height='128' viewBox='0 0 128 128'%3E%3Crect width='128' height='128' rx='24' fill='%230b5ed7'/%3E%3Ctext x='50%25' y='54%25' dominant-baseline='middle' text-anchor='middle' font-size='64' fill='white'%3EB%3C/text%3E%3Ctext x='50%25' y='88%25' dominant-baseline='middle' text-anchor='middle' font-size='28' fill='white'%3EMI%3C/text%3E%3C/svg%3E">
  <meta name="description" content="Clean, subfolder-safe BMI calculator with Bootstrap, jQuery, and Chart.js">
</head>