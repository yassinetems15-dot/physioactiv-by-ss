<?php
session_start();

// === Sécurité : seul l'admin connecté peut chercher ===
if (!isset($_SESSION['admin'])) {
  http_response_code(403);
  echo '<tr><td colspan="8" class="vide">Non autorisé</td></tr>';
  exit();
}

include 'connexion.php';
include 'ligne_message.php'; // ← la fonction partagée genererLigneMessage()

// === Récupérer les paramètres ===
$terme = isset($_GET['terme']) ? mysqli_real_escape_string($conn, trim($_GET['terme'])) : '';
$vue = (isset($_GET['vue']) && $_GET['vue'] === 'corbeille') ? 'corbeille' : 'principale';

// === Filtre selon la vue (active ou corbeille) ===
$filtre_vue = ($vue === 'corbeille') ? 'supprime = 1' : 'supprime = 0';

// === Requête de recherche dans TOUTE la base ===
// On cherche dans nom, email et téléphone (PAS dans le message → recherche de personnes)
if ($terme !== '') {
  $sql = "SELECT * FROM messages
          WHERE $filtre_vue
          AND (nom LIKE '%$terme%'
               OR email LIKE '%$terme%'
               OR telephone LIKE '%$terme%')
          ORDER BY date_envoi DESC";
} else {
  // Si terme vide, on renvoie tout (limité pour la performance)
  $sql = "SELECT * FROM messages WHERE $filtre_vue ORDER BY date_envoi DESC LIMIT 50";
}

$result = mysqli_query($conn, $sql);

// === Générer les lignes HTML ===
if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    echo genererLigneMessage($row, $vue); // ← on réutilise la fonction partagée
  }
} else {
  // Aucun résultat trouvé
  echo '<tr><td colspan="8" class="vide">';
  echo '<i class="fas fa-search"></i>';
  echo '<p>Aucun résultat pour « ' . htmlspecialchars($terme) . ' »</p>';
  echo '</td></tr>';
}
?>