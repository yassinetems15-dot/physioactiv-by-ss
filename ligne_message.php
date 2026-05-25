<?php
/*
 * ============================================================
 *  Fonction partagée : génère une ligne <tr> du tableau messages
 *
 *  Utilisée à la fois par admin.php (affichage normal) et
 *  recherche.php (résultats de recherche AJAX).
 *
 *  Principe DRY (Don't Repeat Yourself) : on écrit la structure
 *  d'une ligne UNE SEULE FOIS, réutilisée à deux endroits.
 *  Si on modifie une ligne, on le fait ici et c'est répercuté partout.
 * ============================================================
 */

function genererLigneMessage($row, $vue) {
  // On capture le HTML généré dans une variable (output buffering)
  ob_start();
  ?>
  <tr class="<?= ($row['lu'] == 0 && $vue === 'principale') ? 'non-lu' : '' ?>">
    <td class="checkbox-col">
      <input type="checkbox" name="selection[]" value="<?= $row['id'] ?>" class="row-checkbox" />
    </td>
    <td>#<?= $row['id'] ?></td>
    <td>
      <span class="nom-patient"><?= htmlspecialchars($row['nom']) ?></span>
      <?php if ($row['lu'] == 0 && $vue === 'principale'): ?>
        <span class="badge-nouveau">Nouveau</span>
      <?php endif; ?>
      <?php if (!empty($row['note'])): ?>
        <i class="fas fa-sticky-note has-note-icon" title="Note interne présente"></i>
      <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($row['telephone']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td class="message-preview"><?= htmlspecialchars($row['message']) ?></td>
    <td><?= date('d/m/Y H:i', strtotime($row['date_envoi'])) ?></td>
    <td>
      <div class="actions">

        <?php if ($vue === 'principale'): ?>
          <button type="button" class="action-btn voir" title="Voir le détail"
            onclick="ouvrirModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
            <i class="fas fa-magnifying-glass"></i>
          </button>

          <a class="action-btn email" title="Répondre par Email (Gmail)"
             href="https://mail.google.com/mail/?view=cm&fs=1&to=<?= urlencode($row['email']) ?>&su=<?= urlencode('Re: Votre demande - PhysioActiv') ?>&body=<?= urlencode("Bonjour " . $row['nom'] . ",\n\n") ?>"
             target="_blank" rel="noopener noreferrer">
            <i class="fas fa-envelope"></i>
          </a>

          <a class="action-btn whatsapp" title="Répondre par WhatsApp"
             href="https://wa.me/212<?= preg_replace('/^0/', '', $row['telephone']) ?>?text=<?= urlencode("Bonjour " . $row['nom'] . ", c'est Sara de PhysioActiv. ") ?>"
             target="_blank" rel="noopener noreferrer">
            <i class="fab fa-whatsapp"></i>
          </a>

          <a class="action-btn appel" title="Appeler"
             href="tel:<?= htmlspecialchars($row['telephone']) ?>"
             target="_blank" rel="noopener noreferrer">
            <i class="fas fa-phone"></i>
          </a>

          <?php if ($row['lu'] == 0): ?>
            <a class="action-btn lu" title="Marquer comme lu" href="admin.php?marquer_lu=<?= $row['id'] ?>">
              <i class="fas fa-check"></i>
            </a>
          <?php else: ?>
            <a class="action-btn lu" title="Marquer comme non lu" href="admin.php?marquer_non_lu=<?= $row['id'] ?>">
              <i class="fas fa-eye-slash"></i>
            </a>
          <?php endif; ?>

          <a class="action-btn supprimer" title="Mettre à la corbeille"
            href="admin.php?supprimer=<?= $row['id'] ?>"
            onclick="return confirm('Mettre ce message à la corbeille ?')">
            <i class="fas fa-trash"></i>
          </a>

        <?php else: ?>
          <button type="button" class="action-btn voir" title="Voir le détail"
            onclick="ouvrirModal(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
            <i class="fas fa-magnifying-glass"></i>
          </button>

          <a class="action-btn restaurer" title="Restaurer le message"
             href="admin.php?restaurer=<?= $row['id'] ?>">
            <i class="fas fa-undo"></i>
          </a>

          <a class="action-btn delete-perma" title="Supprimer définitivement"
             href="admin.php?supprimer_definitivement=<?= $row['id'] ?>"
             onclick="return confirm('Supprimer DÉFINITIVEMENT ce message ? Cette action est irréversible.')">
            <i class="fas fa-times"></i>
          </a>
        <?php endif; ?>

      </div>
    </td>
  </tr>
  <?php
  // On récupère tout le HTML capturé et on le retourne
  return ob_get_clean();
}
?>