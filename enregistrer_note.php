<?php
session_start();

// === Sécurité : seul l'admin connecté peut sauvegarder ===
if (!isset($_SESSION['admin'])) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Non autorisé']);
  exit();
}

include 'connexion.php';

// === Vérifier la méthode ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
  exit();
}

// === Récupérer et valider les données ===
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$note = isset($_POST['note']) ? trim($_POST['note']) : '';

if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'ID invalide']);
  exit();
}

// === Mettre à jour la note (avec requête préparée pour la sécurité) ===
// Si la note est vide, on enregistre NULL au lieu d'une chaîne vide
// → différencie "pas de note" (NULL) de "note volontairement vide" (=== '')
$stmt = mysqli_prepare($conn, "UPDATE messages SET note = ? WHERE id = ?");

if ($note === '') {
  $note_param = null;
  mysqli_stmt_bind_param($stmt, "si", $note_param, $id);
} else {
  mysqli_stmt_bind_param($stmt, "si", $note, $id);
}

$succes = mysqli_stmt_execute($stmt);

// === Réponse JSON pour le JavaScript appelant ===
header('Content-Type: application/json');
if ($succes) {
  echo json_encode([
    'success' => true,
    'message' => 'Note enregistrée',
    'note' => $note
  ]);
} else {
  echo json_encode([
    'success' => false,
    'message' => 'Erreur SQL : ' . mysqli_error($conn)
  ]);
}
?>