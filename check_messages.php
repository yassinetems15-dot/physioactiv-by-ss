<?php
session_start();

// === Sécurité : seul l'admin connecté peut interroger ===
if (!isset($_SESSION['admin'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Non autorisé']);
  exit();
}

include 'connexion.php';

// === Requête optimisée : COUNT plutôt que SELECT * ===
// COUNT(*) ne ramène pas toutes les lignes, juste un nombre.
// C'est BEAUCOUP plus rapide quand on a 1000+ messages.
$result = mysqli_query($conn, "SELECT COUNT(*) AS nb FROM messages WHERE supprime = 0 AND lu = 0");
$row = mysqli_fetch_assoc($result);

// === On retourne aussi l'ID du dernier message non lu ===
// → permet au JS de savoir s'il y a un message vraiment NEUF
// (pas juste un même non lu qui traîne depuis tout à l'heure)
$resultDernier = mysqli_query($conn, "SELECT MAX(id) AS dernier_id FROM messages WHERE supprime = 0 AND lu = 0");
$rowDernier = mysqli_fetch_assoc($resultDernier);

header('Content-Type: application/json');
echo json_encode([
  'nb_non_lus' => intval($row['nb']),
  'dernier_id' => intval($rowDernier['dernier_id']),
  'timestamp' => time()
]);
?>