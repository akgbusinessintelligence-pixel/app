<?php
require_once __DIR__ . '/../config.php';

function require_admin(): void {
  if (empty($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
  }
}
