<?php
session_start();

if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'connexion.php';
include 'ligne_message.php'; // fonction partagée genererLigneMessage()

// Vue active : 'principale' (par défaut) ou 'corbeille'
$vue = (isset($_GET['vue']) && $_GET['vue'] === 'corbeille') ? 'corbeille' : 'principale';

// === ACTIONS GROUPÉES (sélection multiple) ===
if (isset($_POST['action_groupee']) && isset($_POST['selection']) && is_array($_POST['selection'])) {
  // Sécurité : on transforme tous les IDs en entiers et on ne garde que ceux > 0
  $ids = array_map('intval', $_POST['selection']);
  $ids = array_filter($ids, function($id) { return $id > 0; });

  if (!empty($ids)) {
    $ids_str = implode(',', $ids);

    switch ($_POST['action_groupee']) {
      case 'corbeille':
        mysqli_query($conn, "UPDATE messages SET supprime = 1, date_suppression = NOW() WHERE id IN ($ids_str)");
        header("Location: admin.php");
        exit();

      case 'restaurer':
        mysqli_query($conn, "UPDATE messages SET supprime = 0, date_suppression = NULL WHERE id IN ($ids_str)");
        header("Location: admin.php?vue=corbeille");
        exit();

      case 'supprimer_def':
        mysqli_query($conn, "DELETE FROM messages WHERE id IN ($ids_str)");
        header("Location: admin.php?vue=corbeille");
        exit();
    }
  }
}

// === ACTIONS INDIVIDUELLES ===

// Marquer comme lu
if (isset($_GET['marquer_lu'])) {
  $id = intval($_GET['marquer_lu']);
  mysqli_query($conn, "UPDATE messages SET lu = 1 WHERE id = $id");
  header("Location: admin.php");
  exit();
}

// Marquer comme non lu
if (isset($_GET['marquer_non_lu'])) {
  $id = intval($_GET['marquer_non_lu']);
  mysqli_query($conn, "UPDATE messages SET lu = 0 WHERE id = $id");
  header("Location: admin.php");
  exit();
}

// Mettre à la corbeille (suppression douce)
if (isset($_GET['supprimer'])) {
  $id = intval($_GET['supprimer']);
  mysqli_query($conn, "UPDATE messages SET supprime = 1, date_suppression = NOW() WHERE id = $id");
  header("Location: admin.php");
  exit();
}

// Restaurer depuis la corbeille
if (isset($_GET['restaurer'])) {
  $id = intval($_GET['restaurer']);
  mysqli_query($conn, "UPDATE messages SET supprime = 0, date_suppression = NULL WHERE id = $id");
  header("Location: admin.php?vue=corbeille");
  exit();
}

// Supprimer définitivement
if (isset($_GET['supprimer_definitivement'])) {
  $id = intval($_GET['supprimer_definitivement']);
  mysqli_query($conn, "DELETE FROM messages WHERE id = $id");
  header("Location: admin.php?vue=corbeille");
  exit();
}

// === PAGINATION ===
$par_page = 20; // nombre de messages affichés par page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $par_page;

// === REQUÊTE PRINCIPALE ===
$recherche = isset($_GET['recherche']) ? mysqli_real_escape_string($conn, $_GET['recherche']) : '';
$filtre_vue = ($vue === 'corbeille') ? 'supprime = 1' : 'supprime = 0';

// On construit la clause WHERE UNE SEULE FOIS (réutilisée pour le COUNT et le SELECT)
$where = $filtre_vue;
if ($recherche !== '') {
  $where .= " AND (nom LIKE '%$recherche%' OR email LIKE '%$recherche%' OR telephone LIKE '%$recherche%')";
}

// Compter le nombre total de messages correspondants → pour calculer le nombre de pages
$resCount = mysqli_query($conn, "SELECT COUNT(*) AS total FROM messages WHERE $where");
$totalMessages = intval(mysqli_fetch_assoc($resCount)['total']);
$totalPages = max(1, ceil($totalMessages / $par_page));

// Sécurité : si la page demandée dépasse le total (ex. après suppression), on revient à la dernière
if ($page > $totalPages) {
  $page = $totalPages;
  $offset = ($page - 1) * $par_page;
}

// Requête principale avec LIMIT (combien) et OFFSET (à partir d'où)
$sql = "SELECT * FROM messages WHERE $where ORDER BY date_envoi DESC LIMIT $par_page OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Fonction helper : construit l'URL d'une page en conservant la vue et la recherche
function urlPage($numPage, $vue, $recherche) {
  $params = [];
  if ($vue === 'corbeille') $params['vue'] = 'corbeille';
  if ($recherche !== '') $params['recherche'] = $recherche;
  $params['page'] = $numPage;
  return 'admin.php?' . http_build_query($params);
}


// === STATISTIQUES (uniquement sur les messages actifs) ===
$total = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages WHERE supprime = 0"));
$nonLus = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages WHERE supprime = 0 AND lu = 0"));
$lus = $total - $nonLus;
$aujourdhui = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages WHERE supprime = 0 AND DATE(date_envoi) = CURDATE()"));
$nbCorbeille = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM messages WHERE supprime = 1"));

// === DONNÉES DU GRAPHIQUE : nombre de messages par jour (30 derniers jours) ===

// 1. On récupère depuis la base les jours où il y a eu au moins 1 message
$sqlGraph = "SELECT DATE(date_envoi) AS jour, COUNT(*) AS nb
             FROM messages
             WHERE supprime = 0
             AND date_envoi >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(date_envoi)
             ORDER BY jour ASC";
$resGraph = mysqli_query($conn, $sqlGraph);

// On range les résultats dans un tableau associatif : ['2026-05-12' => 3, ...]
$dataParJour = [];
while ($r = mysqli_fetch_assoc($resGraph)) {
  $dataParJour[$r['jour']] = intval($r['nb']);
}

// 2. On génère les 30 derniers jours (incluant ceux à 0 message)
$graphLabels = [];
$graphValues = [];
for ($i = 29; $i >= 0; $i--) {
  $date = date('Y-m-d', strtotime("-$i days"));
  $graphLabels[] = date('d/m', strtotime($date)); // format français court : "12/05"
  $graphValues[] = isset($dataParJour[$date]) ? $dataParJour[$date] : 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>PhysioActiv - Espace Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Segoe UI', sans-serif;
      background: #0d0d1a;
      color: white;
      min-height: 100vh;
    }

    /* HEADER */
    header {
      background: rgba(13, 13, 26, 0.95);
      border-bottom: 1px solid rgba(26, 107, 181, 0.3);
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(10px);
    }

    .header-left { display: flex; align-items: center; gap: 12px; }
    .header-left i { color: #1a6bb5; font-size: 24px; }
    header h1 { font-size: 20px; font-weight: 600; }
    header h1 span { color: #1a6bb5; }

    .btn-logout {
      background: rgba(231, 76, 60, 0.1);
      color: #e74c3c;
      border: 1px solid rgba(231, 76, 60, 0.3);
      padding: 8px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-logout:hover { background: #e74c3c; color: white; }

    /* CLOCHE DE NOTIFICATION DANS LE HEADER */
    .header-actions {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .notification-bell {
      position: relative;
      background: rgba(26, 107, 181, 0.1);
      border: 1px solid rgba(26, 107, 181, 0.3);
      color: #1a6bb5;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 16px;
      transition: all 0.3s;
    }

    .notification-bell:hover {
      background: #1a6bb5;
      color: white;
    }

    .notification-bell.active {
      background: rgba(245, 166, 35, 0.15);
      border-color: #f5a623;
      color: #f5a623;
      animation: bellShake 1.5s ease-in-out infinite;
    }

    @keyframes bellShake {
      0%, 100% { transform: rotate(0deg); }
      10%, 30% { transform: rotate(-12deg); }
      20%, 40% { transform: rotate(12deg); }
      50% { transform: rotate(0deg); }
    }

    .notification-bell .badge {
      position: absolute;
      top: -6px;
      right: -6px;
      background: #e74c3c;
      color: white;
      font-size: 11px;
      font-weight: 700;
      min-width: 20px;
      height: 20px;
      border-radius: 10px;
      padding: 0 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #0d0d1a;
      animation: badgePulse 2s ease-in-out infinite;
    }

    @keyframes badgePulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }

    .notification-bell .badge.hidden {
      display: none;
    }

    /* Toast notification (apparait en haut à droite quand un message arrive) */
    .toast-notification {
      position: fixed;
      top: 90px;
      right: 30px;
      background: rgba(26, 107, 181, 0.95);
      color: white;
      padding: 16px 22px;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(26, 107, 181, 0.5);
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 14px;
      font-weight: 500;
      z-index: 10000;
      transform: translateX(450px);
      transition: transform 0.4s ease;
      backdrop-filter: blur(10px);
      max-width: 380px;
    }

    .toast-notification.show {
      transform: translateX(0);
    }

    .toast-notification i {
      font-size: 22px;
      color: #f5a623;
    }

    .container { padding: 40px; max-width: 1400px; margin: 0 auto; }

    /* TITRE */
    .page-title {
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      gap: 20px;
      flex-wrap: wrap;
    }

    .page-title h2 { font-size: 32px; margin-bottom: 8px; }
    .page-title p { color: #aaa; font-size: 14px; }

    .btn-corbeille {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.3);
      color: #aaa;
      padding: 10px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }

    .btn-corbeille:hover {
      border-color: rgba(26, 107, 181, 0.6);
      color: white;
      transform: translateY(-2px);
    }

    .btn-corbeille .compteur {
      background: #1a6bb5;
      color: white;
      font-size: 11px;
      padding: 2px 7px;
      border-radius: 10px;
      font-weight: 700;
      margin-left: 4px;
    }

    /* STATS */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      border-radius: 14px;
      padding: 22px 24px;
      transition: all 0.3s;
    }

    .stat-card:hover {
      border-color: rgba(26, 107, 181, 0.5);
      transform: translateY(-3px);
    }

    .stat-card .icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-bottom: 14px;
    }

    .stat-card.total .icon { background: rgba(26, 107, 181, 0.15); color: #1a6bb5; }
    .stat-card.nouveau .icon { background: rgba(245, 166, 35, 0.15); color: #f5a623; }
    .stat-card.lu .icon { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }
    .stat-card.aujourdhui .icon { background: rgba(155, 89, 182, 0.15); color: #9b59b6; }

    .stat-card .nombre { font-size: 32px; font-weight: 700; margin-bottom: 4px; }
    .stat-card .label {
      color: #aaa;
      font-size: 13px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    /* GRAPHIQUE D'ACTIVITÉ */
    .chart-container {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      border-radius: 14px;
      padding: 24px 28px;
      margin-bottom: 30px;
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .chart-header h3 {
      font-size: 16px;
      font-weight: 600;
      color: white;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .chart-header h3 i {
      color: #1a6bb5;
    }

    .chart-header .periode {
      color: #888;
      font-size: 12px;
    }

    .chart-wrapper {
      position: relative;
      height: 240px;
    }

    /* BOUTON FLOTTANT D'EXPORT (Floating Action Button) */
    .fab-export {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 56px;
      height: 56px;
      background: #1a6bb5;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      font-size: 20px;
      box-shadow: 0 6px 20px rgba(26, 107, 181, 0.45);
      transition: all 0.3s;
      z-index: 99;
    }

    .fab-export:hover {
      background: #155a9a;
      transform: translateY(-4px) scale(1.05);
      box-shadow: 0 10px 28px rgba(26, 107, 181, 0.6);
    }

    /* Infobulle au survol */
    .fab-export .fab-tooltip {
      position: absolute;
      right: 70px;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(13, 13, 26, 0.95);
      border: 1px solid rgba(26, 107, 181, 0.4);
      color: white;
      padding: 8px 14px;
      border-radius: 8px;
      font-size: 13px;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s, transform 0.3s;
    }

    .fab-export:hover .fab-tooltip {
      opacity: 1;
      transform: translateY(-50%) translateX(-4px);
    }

    /* RECHERCHE */
    .search-bar {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      border-radius: 12px;
      padding: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
      max-width: 500px;
    }

    .search-bar i { color: #1a6bb5; padding-left: 14px; }

    .search-bar input {
      flex: 1;
      background: transparent;
      border: none;
      padding: 12px 8px;
      color: white;
      font-size: 14px;
      outline: none;
    }

    .search-bar input::placeholder { color: #888; }

    .search-bar button {
      background: #1a6bb5;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
    }

    .search-bar button:hover { background: #155a9a; }

    /* BARRE D'ACTIONS GROUPÉES */
    .bulk-actions {
      background: rgba(26, 107, 181, 0.1);
      border: 1px solid rgba(26, 107, 181, 0.3);
      border-radius: 12px;
      padding: 14px 20px;
      margin-bottom: 20px;
      display: none;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      animation: slideDown 0.3s ease;
    }

    .bulk-actions.actif { display: flex; }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-8px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .bulk-count {
      color: #ddd;
      font-size: 14px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bulk-count #bulkCount {
      color: white;
      background: #1a6bb5;
      padding: 4px 10px;
      border-radius: 8px;
      font-weight: 700;
      min-width: 32px;
      text-align: center;
    }

    .bulk-buttons { display: flex; gap: 8px; flex-wrap: wrap; }

    .bulk-btn {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #ddd;
      padding: 9px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.3s;
      font-family: inherit;
    }

    .bulk-btn:hover { transform: translateY(-2px); }

    .bulk-trash { background: rgba(231, 76, 60, 0.15); color: #e74c3c; border-color: rgba(231, 76, 60, 0.3); }
    .bulk-trash:hover { background: #e74c3c; color: white; }

    .bulk-restore { background: rgba(46, 204, 113, 0.15); color: #2ecc71; border-color: rgba(46, 204, 113, 0.3); }
    .bulk-restore:hover { background: #2ecc71; color: white; }

    .bulk-delete { background: rgba(192, 57, 43, 0.15); color: #c0392b; border-color: rgba(192, 57, 43, 0.3); }
    .bulk-delete:hover { background: #c0392b; color: white; }

    .bulk-cancel:hover { background: rgba(255, 255, 255, 0.1); color: white; }

    /* TABLEAU */
    .table-container {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      border-radius: 14px;
      overflow: hidden;
      min-height: 600px;
    }

    /* PAGINATION */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      margin-top: 24px;
      flex-wrap: wrap;
    }

    .page-btn, .page-num {
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.3);
      color: #ddd;
      padding: 9px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 600;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
    }

    .page-btn:hover, .page-num:hover {
      background: rgba(26, 107, 181, 0.2);
      border-color: rgba(26, 107, 181, 0.6);
      color: white;
      transform: translateY(-2px);
    }

    .page-num.active {
      background: #1a6bb5;
      border-color: #1a6bb5;
      color: white;
    }

    .page-num.active:hover {
      transform: none;
    }

    .page-btn.disabled {
      opacity: 0.35;
      cursor: not-allowed;
      pointer-events: none;
    }

    .page-numbers {
      display: flex;
      gap: 6px;
      flex-wrap: wrap;
    }

    .pagination-info {
      text-align: center;
      color: #888;
      font-size: 12px;
      margin-top: 14px;
    }

    table { width: 100%; border-collapse: collapse; }

    thead {
      background: rgba(26, 107, 181, 0.1);
      border-bottom: 1px solid rgba(26, 107, 181, 0.3);
    }

    th {
      padding: 16px;
      text-align: left;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #1a6bb5;
      font-weight: 600;
    }

    td {
      padding: 16px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      font-size: 14px;
      color: #ddd;
      vertical-align: middle;
    }

    tr:last-child td { border-bottom: none; }
    tr.non-lu td { background: rgba(26, 107, 181, 0.05); }
    tr.non-lu td:first-child { border-left: 3px solid #1a6bb5; }
    tr:hover td { background: rgba(255, 255, 255, 0.02); }

    /* Colonne checkboxes */
    .checkbox-col {
      width: 44px;
      text-align: center;
      padding-left: 16px;
      padding-right: 0;
    }

    .row-checkbox, #selectAll {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: #1a6bb5;
    }

    .badge-nouveau {
      display: inline-block;
      background: #f5a623;
      color: white;
      font-size: 10px;
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-left: 8px;
    }

    .nom-patient { font-weight: 600; color: white; }

    .message-preview {
      max-width: 300px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .actions { display: flex; gap: 8px; }

    .action-btn {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.1);
      color: #aaa;
      width: 34px;
      height: 34px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: all 0.3s;
      font-size: 13px;
    }

    .action-btn:hover { transform: translateY(-2px); }
    .action-btn.voir:hover { background: #1a6bb5; color: white; border-color: #1a6bb5; }
    .action-btn.lu:hover { background: #2ecc71; color: white; border-color: #2ecc71; }
    .action-btn.supprimer:hover { background: #e74c3c; color: white; border-color: #e74c3c; }
    .action-btn.email:hover { background: #f5a623; color: white; border-color: #f5a623; }
    .action-btn.whatsapp:hover { background: #25d366; color: white; border-color: #25d366; }
    .action-btn.appel:hover { background: #1a6bb5; color: white; border-color: #1a6bb5; }
    .action-btn.restaurer:hover { background: #2ecc71; color: white; border-color: #2ecc71; }
    .action-btn.delete-perma:hover { background: #c0392b; color: white; border-color: #c0392b; }

    .vide { text-align: center; padding: 60px; color: #888; }

    .vide i {
      font-size: 48px;
      color: #1a6bb5;
      opacity: 0.3;
      margin-bottom: 16px;
      display: block;
    }

    /* MODAL */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      z-index: 1000;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }

    .modal-overlay.actif { display: flex; }

    .modal {
      background: #1a1a2e;
      border: 1px solid rgba(26, 107, 181, 0.3);
      border-radius: 14px;
      padding: 32px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 18px;
      border-bottom: 1px solid rgba(26, 107, 181, 0.2);
    }

    .modal-header h3 { color: white; font-size: 20px; }

    .modal-close {
      background: transparent;
      border: none;
      color: #888;
      font-size: 24px;
      cursor: pointer;
    }

    .modal-info {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
      margin-bottom: 20px;
    }

    .info-bloc {
      background: rgba(255, 255, 255, 0.03);
      padding: 14px 16px;
      border-radius: 10px;
    }

    .info-bloc .label {
      color: #1a6bb5;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 4px;
    }

    .info-bloc .valeur { color: white; font-size: 14px; }
    .info-bloc.full { grid-column: span 2; }

    .info-bloc .message-full {
      color: #ddd;
      font-size: 14px;
      line-height: 1.6;
      white-space: pre-wrap;
    }

    /* SECTION NOTE INTERNE DANS LE MODAL */
    .note-section {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid rgba(26, 107, 181, 0.2);
    }

    .note-section .label {
      color: #1a6bb5;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .note-section textarea {
      width: 100%;
      min-height: 80px;
      background: rgba(255, 255, 255, 0.03);
      border: 1px solid rgba(26, 107, 181, 0.2);
      border-radius: 10px;
      padding: 12px 14px;
      color: white;
      font-family: inherit;
      font-size: 14px;
      resize: vertical;
      outline: none;
      transition: border-color 0.3s;
    }

    .note-section textarea:focus {
      border-color: rgba(26, 107, 181, 0.6);
      background: rgba(26, 107, 181, 0.05);
    }

    .note-section textarea::placeholder {
      color: #666;
      font-style: italic;
    }

    .note-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 12px;
    }

    .note-status {
      font-size: 12px;
      color: #888;
    }

    .note-status.success { color: #2ecc71; }
    .note-status.error { color: #e74c3c; }

    .btn-save-note {
      background: #1a6bb5;
      color: white;
      border: none;
      padding: 8px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }

    .btn-save-note:hover {
      background: #155a9a;
      transform: translateY(-1px);
    }

    .btn-save-note:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    /* Petit icône note dans le tableau */
    .has-note-icon {
      color: #f5a623;
      font-size: 12px;
      margin-left: 6px;
      cursor: help;
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
      header { padding: 16px 20px; }
      header h1 { font-size: 16px; }
      .container { padding: 20px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .modal-info { grid-template-columns: 1fr; }
      .info-bloc.full { grid-column: span 1; }
      table { font-size: 12px; }
      th, td { padding: 10px 8px; }
      .message-preview { max-width: 150px; }
      .bulk-actions { padding: 12px; }
      .bulk-buttons { width: 100%; }
      .bulk-btn { flex: 1; justify-content: center; }
    }
  </style>
</head>
<body>

  <header>
    <div class="header-left">
      <i class="fas fa-stethoscope"></i>
      <h1>PhysioActiv <span>By SS</span> — Admin</h1>
    </div>
    <div class="header-actions">
      <button type="button" class="notification-bell" id="notificationBell" title="Notifications" onclick="basculerNotifications()">
        <i class="fas fa-bell"></i>
        <span class="badge hidden" id="notificationBadge">0</span>
      </button>
      <a href="logout.php" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Se déconnecter
      </a>
    </div>
  </header>

  <!-- Toast notification (apparait quand un nouveau message arrive) -->
  <div class="toast-notification" id="toastNotification">
    <i class="fas fa-bell"></i>
    <div>
      <strong>Nouveau message reçu !</strong>
      <div id="toastMessage" style="font-size: 12px; opacity: 0.9; margin-top: 2px;"></div>
    </div>
  </div>

  <div class="container">

    <div class="page-title">
      <div>
        <?php if ($vue === 'corbeille'): ?>
          <h2><i class="fas fa-trash"></i> Corbeille</h2>
          <p>Messages supprimés — vous pouvez les restaurer ou les supprimer définitivement.</p>
        <?php else: ?>
          <h2>Tableau de bord</h2>
          <p>
            <?php if ($nonLus > 0): ?>
              Vous avez <strong style="color:#f5a623;"><?= $nonLus ?> nouveau<?= $nonLus > 1 ? 'x' : '' ?> message<?= $nonLus > 1 ? 's' : '' ?></strong> à consulter
            <?php else: ?>
              Tous vos messages sont à jour ✓
            <?php endif; ?>
          </p>
        <?php endif; ?>
      </div>

      <?php if ($vue === 'corbeille'): ?>
        <a href="admin.php" class="btn-corbeille">
          <i class="fas fa-arrow-left"></i> Retour aux messages
        </a>
      <?php else: ?>
        <a href="admin.php?vue=corbeille" class="btn-corbeille">
          <i class="fas fa-trash"></i> Corbeille
          <?php if ($nbCorbeille > 0): ?>
            <span class="compteur"><?= $nbCorbeille ?></span>
          <?php endif; ?>
        </a>
      <?php endif; ?>
    </div>

    <!-- Statistiques (uniquement sur la vue principale) -->
    <?php if ($vue === 'principale'): ?>
    <div class="stats-grid">
      <div class="stat-card total">
        <div class="icon"><i class="fas fa-envelope"></i></div>
        <div class="nombre"><?= $total ?></div>
        <div class="label">Total messages</div>
      </div>
      <div class="stat-card nouveau">
        <div class="icon"><i class="fas fa-bell"></i></div>
        <div class="nombre"><?= $nonLus ?></div>
        <div class="label">Non lus</div>
      </div>
      <div class="stat-card lu">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div class="nombre"><?= $lus ?></div>
        <div class="label">Lus</div>
      </div>
      <div class="stat-card aujourdhui">
        <div class="icon"><i class="fas fa-calendar-day"></i></div>
        <div class="nombre"><?= $aujourdhui ?></div>
        <div class="label">Aujourd'hui</div>
      </div>
    </div>

    <!-- Graphique d'activité -->
    <div class="chart-container">
      <div class="chart-header">
        <h3><i class="fas fa-chart-line"></i> Activité des messages</h3>
        <span class="periode">30 derniers jours</span>
      </div>
      <div class="chart-wrapper">
        <canvas id="chartActivite"></canvas>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recherche (formulaire indépendant en GET) -->
    <form method="GET" class="search-bar">
      <?php if ($vue === 'corbeille'): ?>
        <input type="hidden" name="vue" value="corbeille">
      <?php endif; ?>
      <i class="fas fa-search"></i>
      <input type="text" name="recherche" placeholder="Rechercher par nom, email ou téléphone..." value="<?= htmlspecialchars($recherche) ?>" />
      <button type="submit">Rechercher</button>
    </form>

    <!-- Formulaire de sélection multiple (en POST) -->
    <form method="POST" action="admin.php<?= $vue === 'corbeille' ? '?vue=corbeille' : '' ?>" id="form-selection">

      <!-- Barre d'actions groupées (cachée par défaut) -->
      <div class="bulk-actions" id="bulkActions">
        <div class="bulk-count">
          <span id="bulkCount">0</span>
          <span>message(s) sélectionné(s)</span>
        </div>
        <div class="bulk-buttons">
          <?php if ($vue === 'principale'): ?>
            <button type="submit" name="action_groupee" value="corbeille" class="bulk-btn bulk-trash"
                    onclick="return confirm('Mettre les messages sélectionnés à la corbeille ?')">
              <i class="fas fa-trash"></i> Mettre à la corbeille
            </button>
          <?php else: ?>
            <button type="submit" name="action_groupee" value="restaurer" class="bulk-btn bulk-restore">
              <i class="fas fa-undo"></i> Restaurer
            </button>
            <button type="submit" name="action_groupee" value="supprimer_def" class="bulk-btn bulk-delete"
                    onclick="return confirm('Supprimer DÉFINITIVEMENT les messages sélectionnés ? Cette action est irréversible.')">
              <i class="fas fa-times"></i> Supprimer définitivement
            </button>
          <?php endif; ?>
          <button type="button" class="bulk-btn bulk-cancel" onclick="decocherTout()">
            Annuler
          </button>
        </div>
      </div>

      <!-- Tableau -->
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th class="checkbox-col">
                <input type="checkbox" id="selectAll" title="Tout sélectionner" />
              </th>
              <th>#</th>
              <th>Patient</th>
              <th>Téléphone</th>
              <th>Email</th>
              <th>Message</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="messagesTableBody">
            <?php if (mysqli_num_rows($result) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?= genererLigneMessage($row, $vue) ?>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="8" class="vide">
                <i class="fas <?= $vue === 'corbeille' ? 'fa-trash' : 'fa-inbox' ?>"></i>
                <p>
                  <?php if ($vue === 'corbeille'): ?>
                    La corbeille est vide.
                  <?php else: ?>
                    Aucun message reçu pour le moment.
                  <?php endif; ?>
                </p>
              </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    </form>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <!-- Bouton Précédent -->
        <?php if ($page > 1): ?>
          <a href="<?= urlPage($page - 1, $vue, $recherche) ?>" class="page-btn">
            <i class="fas fa-chevron-left"></i> Précédent
          </a>
        <?php else: ?>
          <span class="page-btn disabled"><i class="fas fa-chevron-left"></i> Précédent</span>
        <?php endif; ?>

        <!-- Numéros de page -->
        <div class="page-numbers">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?= urlPage($i, $vue, $recherche) ?>" class="page-num <?= $i === $page ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>

        <!-- Bouton Suivant -->
        <?php if ($page < $totalPages): ?>
          <a href="<?= urlPage($page + 1, $vue, $recherche) ?>" class="page-btn">
            Suivant <i class="fas fa-chevron-right"></i>
          </a>
        <?php else: ?>
          <span class="page-btn disabled">Suivant <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
      </div>

      <div class="pagination-info">
        Page <?= $page ?> sur <?= $totalPages ?> — <?= $totalMessages ?> message<?= $totalMessages > 1 ? 's' : '' ?> au total
      </div>
    <?php endif; ?>

  </div>

  <!-- Modal Détails -->
  <div class="modal-overlay" id="modalDetails">
    <div class="modal">
      <div class="modal-header">
        <h3><i class="fas fa-envelope-open"></i> Détails du message</h3>
        <button type="button" class="modal-close" onclick="fermerModal()">&times;</button>
      </div>
      <div class="modal-info">
        <div class="info-bloc">
          <div class="label">Nom du patient</div>
          <div class="valeur" id="modal-nom"></div>
        </div>
        <div class="info-bloc">
          <div class="label">Téléphone</div>
          <div class="valeur" id="modal-tel"></div>
        </div>
        <div class="info-bloc full">
          <div class="label">Email</div>
          <div class="valeur" id="modal-email"></div>
        </div>
        <div class="info-bloc full">
          <div class="label">Message</div>
          <div class="message-full" id="modal-message"></div>
        </div>
        <div class="info-bloc full">
          <div class="label">Date d'envoi</div>
          <div class="valeur" id="modal-date"></div>
        </div>
      </div>

      <!-- Section Note Interne -->
      <div class="note-section">
        <div class="label">
          <i class="fas fa-sticky-note"></i> Note interne (privée)
        </div>
        <textarea id="modal-note" placeholder="Écrivez ici vos remarques sur ce message... (visible uniquement par vous)"></textarea>
        <div class="note-actions">
          <span class="note-status" id="note-status"></span>
          <button type="button" class="btn-save-note" id="btn-save-note" onclick="enregistrerNote()">
            <i class="fas fa-save"></i> Enregistrer
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bouton flottant d'export (visible uniquement sur la vue principale) -->
  <?php if ($vue === 'principale'): ?>
    <a href="export.php" class="fab-export" title="Exporter en Excel">
      <i class="fas fa-file-excel"></i>
      <span class="fab-tooltip">Exporter en Excel</span>
    </a>
  <?php endif; ?>

  <script>
    const vueActive = '<?= $vue ?>';

    // === GRAPHIQUE D'ACTIVITÉ (Chart.js) ===
    <?php if ($vue === 'principale'): ?>
    const ctxActivite = document.getElementById('chartActivite');
    if (ctxActivite) {
      new Chart(ctxActivite, {
        type: 'line',
        data: {
          labels: <?= json_encode($graphLabels) ?>,
          datasets: [{
            label: 'Messages reçus',
            data: <?= json_encode($graphValues) ?>,
            borderColor: '#1a6bb5',
            backgroundColor: 'rgba(26, 107, 181, 0.15)',
            borderWidth: 2,
            fill: true,
            tension: 0.35, // courbe lissée (0 = droites cassées, 1 = très arrondi)
            pointBackgroundColor: '#1a6bb5',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }, // pas besoin de légende, un seul dataset
            tooltip: {
              backgroundColor: 'rgba(13, 13, 26, 0.95)',
              borderColor: 'rgba(26, 107, 181, 0.3)',
              borderWidth: 1,
              padding: 12,
              titleColor: '#1a6bb5',
              bodyColor: '#fff',
              callbacks: {
                label: (ctx) => ctx.parsed.y + ' message' + (ctx.parsed.y > 1 ? 's' : '')
              }
            }
          },
          scales: {
            x: {
              ticks: { color: '#888', font: { size: 11 } },
              grid: { color: 'rgba(255, 255, 255, 0.04)' }
            },
            y: {
              beginAtZero: true,
              ticks: {
                color: '#888',
                font: { size: 11 },
                stepSize: 1, // pas de décimales sur l'axe Y
                precision: 0
              },
              grid: { color: 'rgba(255, 255, 255, 0.04)' }
            }
          }
        }
      });
    }
    <?php endif; ?>

    // === MODAL DÉTAILS ===
    let messageIdActuel = null; // ID du message ouvert dans le modal

    function ouvrirModal(data) {
      messageIdActuel = data.id;
      document.getElementById('modal-nom').textContent = data.nom;
      document.getElementById('modal-tel').textContent = data.telephone;
      document.getElementById('modal-email').textContent = data.email;
      document.getElementById('modal-message').textContent = data.message;
      document.getElementById('modal-date').textContent = new Date(data.date_envoi).toLocaleString('fr-FR');

      // Charger la note existante dans le textarea (ou vide si pas de note)
      document.getElementById('modal-note').value = data.note || '';
      document.getElementById('note-status').textContent = '';
      document.getElementById('note-status').className = 'note-status';

      document.getElementById('modalDetails').classList.add('actif');

      // Marquer comme lu automatiquement (seulement sur la vue principale)
      if (data.lu == 0 && vueActive === 'principale') {
        setTimeout(() => {
          window.location.href = 'admin.php?marquer_lu=' + data.id;
        }, 2000);
      }
    }

    // === ENREGISTRER UNE NOTE (AJAX) ===
    function enregistrerNote() {
      if (!messageIdActuel) return;

      const note = document.getElementById('modal-note').value;
      const btn = document.getElementById('btn-save-note');
      const status = document.getElementById('note-status');

      // Désactiver le bouton pendant la requête
      btn.disabled = true;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
      status.textContent = '';
      status.className = 'note-status';

      // Préparer les données du formulaire
      const formData = new FormData();
      formData.append('id', messageIdActuel);
      formData.append('note', note);

      // Envoi de la requête AJAX vers enregistrer_note.php
      fetch('enregistrer_note.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            status.textContent = '✓ Note enregistrée';
            status.className = 'note-status success';
            // Le statut s'efface après 3 secondes
            setTimeout(() => { status.textContent = ''; }, 3000);
          } else {
            status.textContent = '✗ ' + (data.message || 'Erreur');
            status.className = 'note-status error';
          }
        })
        .catch(err => {
          status.textContent = '✗ Erreur réseau';
          status.className = 'note-status error';
          console.error(err);
        })
        .finally(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
        });
    }

    function fermerModal() {
      document.getElementById('modalDetails').classList.remove('actif');
    }

    document.getElementById('modalDetails').addEventListener('click', (e) => {
      if (e.target.id === 'modalDetails') fermerModal();
    });

    // === SÉLECTION MULTIPLE ===
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const bulkCount = document.getElementById('bulkCount');

    function majBarreActions() {
      const cochees = document.querySelectorAll('.row-checkbox:checked').length;
      bulkCount.textContent = cochees;

      // Afficher / cacher la barre
      if (cochees > 0) {
        bulkActions.classList.add('actif');
      } else {
        bulkActions.classList.remove('actif');
      }

      // Mettre à jour la case "tout sélectionner" :
      // - cochée si toutes le sont
      // - indéterminée si seulement quelques-unes
      // - décochée si aucune
      if (cochees === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
      } else if (cochees === checkboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
      } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
      }
    }

    // Clic sur "tout sélectionner"
    if (selectAll) {
      selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        majBarreActions();
      });
    }

    // Clic sur chaque case individuelle
    checkboxes.forEach(cb => {
      cb.addEventListener('change', majBarreActions);
    });

    // ═══════════════════════════════════════════════════════
    // RECHERCHE INSTANTANÉE (AJAX + debounce)
    // ═══════════════════════════════════════════════════════
    const champRecherche = document.querySelector('.search-bar input[name="recherche"]');
    const tableBody = document.getElementById('messagesTableBody');
    const blocPagination = document.querySelector('.pagination');
    const blocPaginationInfo = document.querySelector('.pagination-info');

    if (champRecherche && tableBody) {
      // On sauvegarde l'état initial du tableau (page 1 paginée)
      const tableBodyOriginal = tableBody.innerHTML;
      let timerDebounce = null;

      champRecherche.addEventListener('input', function () {
        // On annule le timer précédent à chaque frappe
        clearTimeout(timerDebounce);

        const terme = champRecherche.value.trim();

        // DEBOUNCE : on attend 300ms après la dernière frappe avant de chercher
        // → évite d'envoyer une requête à chaque lettre tapée
        timerDebounce = setTimeout(() => {
          if (terme === '') {
            // Champ vide → on restaure l'affichage paginé original
            tableBody.innerHTML = tableBodyOriginal;
            if (blocPagination) blocPagination.style.display = '';
            if (blocPaginationInfo) blocPaginationInfo.style.display = '';
            return;
          }

          // Indicateur de chargement
          tableBody.innerHTML = '<tr><td colspan="8" class="vide"><i class="fas fa-spinner fa-spin"></i><p>Recherche en cours...</p></td></tr>';

          // Requête AJAX vers recherche.php
          const url = 'recherche.php?terme=' + encodeURIComponent(terme) + '&vue=' + vueActive;
          fetch(url)
            .then(response => response.text())
            .then(html => {
              tableBody.innerHTML = html;
              // On cache la pagination pendant la recherche (les résultats ne sont pas paginés)
              if (blocPagination) blocPagination.style.display = 'none';
              if (blocPaginationInfo) blocPaginationInfo.style.display = 'none';
            })
            .catch(err => {
              tableBody.innerHTML = '<tr><td colspan="8" class="vide">Erreur de recherche</td></tr>';
              console.error(err);
            });
        }, 300);
      });

      // Empêcher le rechargement de page si on appuie sur Entrée
      // (puisque la recherche se fait déjà en temps réel)
      champRecherche.closest('form').addEventListener('submit', function (e) {
        e.preventDefault();
      });
    }

    // ═══════════════════════════════════════════════════════
    // NOTIFICATIONS TEMPS RÉEL (polling toutes les 20 secondes)
    // ═══════════════════════════════════════════════════════

    // État initial : on récupère le compteur affiché actuellement sur la page
    let dernierIdConnu = <?= intval($nonLus) > 0 ? "0" : "0" ?>; // sera mis à jour au premier check
    let nbNonLusInitial = <?= intval($nonLus) ?>;
    let premierCheck = true;

    const bell = document.getElementById('notificationBell');
    const badge = document.getElementById('notificationBadge');
    const toast = document.getElementById('toastNotification');
    const toastMessage = document.getElementById('toastMessage');
    const favicon = document.querySelector('link[rel="icon"]') || document.querySelector('link[rel="shortcut icon"]');
    const titreOriginal = document.title;

    // Initialiser le badge avec le compteur actuel
    if (nbNonLusInitial > 0) {
      badge.textContent = nbNonLusInitial;
      badge.classList.remove('hidden');
      bell.classList.add('active');
      document.title = `(${nbNonLusInitial}) ${titreOriginal}`;
    }

    // === Fonction principale : vérifier s'il y a du nouveau ===
    function verifierMessages() {
      fetch('check_messages.php')
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            console.warn('Polling: non autorisé, arrêt');
            clearInterval(intervalPolling);
            return;
          }

          const nbActuel = data.nb_non_lus;
          const dernierId = data.dernier_id;

          // Au premier check, on mémorise juste l'ID max et on ne notifie pas
          if (premierCheck) {
            dernierIdConnu = dernierId;
            premierCheck = false;
            return;
          }

          // Si un nouveau message est arrivé (ID plus grand qu'avant)
          if (dernierId > dernierIdConnu) {
            const nouveauxMessages = nbActuel - nbNonLusInitial;
            declencherNotification(nouveauxMessages > 0 ? nouveauxMessages : 1);
            dernierIdConnu = dernierId;
          }

          // Mettre à jour le compteur du badge dans tous les cas
          majBadge(nbActuel);
        })
        .catch(err => console.warn('Polling échoué:', err));
    }

    // === Mettre à jour visuellement le badge et le titre ===
    function majBadge(nb) {
      nbNonLusInitial = nb;
      if (nb > 0) {
        badge.textContent = nb > 99 ? '99+' : nb;
        badge.classList.remove('hidden');
        bell.classList.add('active');
        document.title = `(${nb}) ${titreOriginal}`;
      } else {
        badge.classList.add('hidden');
        bell.classList.remove('active');
        document.title = titreOriginal;
      }
    }

    // === Déclencher la notification (son + toast) ===
    function declencherNotification(nb) {
      // 1. Jouer le son "ding"
      jouerSonNotification();

      // 2. Afficher le toast pendant 5 secondes
      toastMessage.textContent = nb > 1
        ? `${nb} nouveaux messages dans votre boîte`
        : `Un nouveau patient vient de vous écrire`;
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 5000);
    }

    // === Son de notification (généré dynamiquement, pas de fichier audio !) ===
    // Utilise Web Audio API pour créer un "ding" léger
    let audioContextGlobal = null;

    // === Déblocage de l'audio au premier clic (politique Chrome) ===
    // Chrome bloque les sons tant que l'utilisateur n'a pas interagi avec la page.
    // On crée et "réveille" l'AudioContext dès la première interaction,
    // comme ça les notifications suivantes pourront jouer le son sans souci.
    document.addEventListener('click', function deblocageAudio() {
      if (!audioContextGlobal) {
        audioContextGlobal = new (window.AudioContext || window.webkitAudioContext)();
        // Si le contexte est suspendu, on le reprend
        if (audioContextGlobal.state === 'suspended') {
          audioContextGlobal.resume();
        }
      }
      // On retire l'écouteur après le premier clic (plus besoin)
      document.removeEventListener('click', deblocageAudio);
    }, { once: true });

    function jouerSonNotification() {
      try {
        // Utiliser le contexte global s'il existe, sinon en créer un
        const audioCtx = audioContextGlobal || new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        // Fréquence : 880 Hz = note La aigu (proche du "ding" Apple)
        oscillator.frequency.value = 880;
        oscillator.type = 'sine'; // son doux

        // Enveloppe : volume qui monte vite puis descend doucement
        gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
        gainNode.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.01);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.5);

        oscillator.start(audioCtx.currentTime);
        oscillator.stop(audioCtx.currentTime + 0.5);
      } catch (e) {
        console.warn('Son indisponible:', e);
      }
    }

    // === Action au clic sur la cloche : recharger la page ===
    function basculerNotifications() {
      if (nbNonLusInitial > 0) {
        window.location.reload();
      }
    }

    // === Démarrer le polling toutes les 20 secondes ===
    const intervalPolling = setInterval(verifierMessages, 20000);

    // Premier check immédiat (pour initialiser dernierIdConnu)
    verifierMessages();
  </script>

</body>
</html>