<?php
session_start();

// === SÉCURITÉ : seul l'admin connecté peut télécharger ===
// Sans ça, n'importe qui tapant l'URL /export.php récupèrerait les données patients !
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'connexion.php';

// === Récupération des messages actifs (hors corbeille) ===
$sql = "SELECT id, nom, telephone, email, message, date_envoi, lu
        FROM messages
        WHERE supprime = 0
        ORDER BY date_envoi DESC";
$result = mysqli_query($conn, $sql);

// === Nom du fichier dynamique avec la date du jour ===
// Ex : "physioactiv_messages_2026-05-14.csv"
$nom_fichier = "physioactiv_messages_" . date('Y-m-d') . ".csv";

// === Headers HTTP pour forcer le téléchargement ===
//  - Content-Type: dit au navigateur que c'est un CSV
//  - Content-Disposition: attachment → force le téléchargement au lieu d'ouvrir dans le navigateur
//  - Pragma/Expires: empêchent le cache (le fichier doit être frais à chaque clic)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Pragma: no-cache');
header('Expires: 0');

// === Ouvrir le flux de sortie PHP comme un fichier ===
// php://output = un "fichier virtuel" qui écrit directement dans la réponse HTTP
$output = fopen('php://output', 'w');

// === Hint Excel : forcer le séparateur point-virgule ===
// Cette ligne magique "sep=;" est lue par Excel comme une directive (pas comme une donnée).
// Sans elle, Excel utilise le séparateur défini dans les paramètres régionaux Windows,
// ce qui peut casser le découpage des colonnes selon les machines.
fwrite($output, "sep=;\r\n");

// === Fonction de conversion UTF-8 → Windows-1252 ===
// Bug connu : quand Excel voit "sep=;" en première ligne, il ignore le BOM UTF-8
// et lit en Windows-1252 (encodage Windows par défaut). Du coup "é" devient "Ã©".
// Solution : on convertit nous-mêmes l'UTF-8 en Windows-1252 avant d'écrire.
// Note : ça gère parfaitement les accents français (é, è, à, ç...) et arabes latins.
function pourExcel($texte) {
  return mb_convert_encoding($texte, 'Windows-1252', 'UTF-8');
}

// === Ligne d'en-tête (noms des colonnes) ===
// fputcsv() s'occupe automatiquement d'échapper les guillemets, virgules, sauts de ligne
// Le 3ème paramètre ";" = séparateur point-virgule (compatible Excel français)
fputcsv($output, [
  'ID',
  pourExcel('Nom'),
  pourExcel('Téléphone'),
  'Email',
  pourExcel('Message'),
  pourExcel('Date d\'envoi'),
  'Statut'
], ';');

// === Lignes de données ===
while ($row = mysqli_fetch_assoc($result)) {
  fputcsv($output, [
    $row['id'],
    pourExcel($row['nom']),
    $row['telephone'],
    $row['email'],
    pourExcel($row['message']),
    date('d/m/Y H:i', strtotime($row['date_envoi'])),
    $row['lu'] == 1 ? 'Lu' : 'Non lu'
  ], ';');
}

fclose($output);
exit();
?>