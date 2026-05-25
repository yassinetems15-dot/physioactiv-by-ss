<?php
include 'connexion.php';

// Inclusion de PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $nom = mysqli_real_escape_string($conn, $_POST['nom']);
  $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $message = mysqli_real_escape_string($conn, $_POST['message']);

  if (!empty($nom) && !empty($telephone) && !empty($email) && !empty($message)) {

    // 1. Enregistrer dans la base de données
    $sql = "INSERT INTO messages (nom, telephone, email, message)
            VALUES ('$nom', '$telephone', '$email', '$message')";

    if (mysqli_query($conn, $sql)) {

      // 2. Envoyer un email de notification
      $mail = new PHPMailer(true);

      try {
        // Configuration du serveur SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yass.tems@gmail.com';        // ← À REMPLACER
        $mail->Password   = 'euptpfskmhnrydhr';       // ← À REMPLACER
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Expéditeur et destinataire
        $mail->setFrom('yass.tems@gmail.com', 'PhysioActiv By SS');
        $mail->addAddress('yass.tems@gmail.com', 'Sara Semlali');

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = 'Nouveau message d\'un patient - PhysioActiv';
        $mail->Body    = "
          <h2 style='color:#1a6bb5;'>Nouveau message reçu</h2>
          <p><strong>Nom :</strong> $nom</p>
          <p><strong>Téléphone :</strong> $telephone</p>
          <p><strong>Email :</strong> $email</p>
          <p><strong>Message :</strong></p>
          <p style='background:#f5f5f5; padding:15px; border-left:4px solid #1a6bb5;'>$message</p>
          <hr>
          <p style='color:#888; font-size:12px;'>Email envoyé automatiquement depuis le site PhysioActiv By SS</p>
        ";

        $mail->send();

        echo json_encode(["status" => "success", "message" => "Message envoyé avec succès !"]);

      } catch (Exception $e) {
        // Le message est enregistré mais l'email a échoué
        echo json_encode(["status" => "success", "message" => "Message enregistré (notification email indisponible)."]);
      }

    } else {
      echo json_encode(["status" => "error", "message" => "Erreur lors de l'envoi."]);
    }

  } else {
    echo json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs."]);
  }

  mysqli_close($conn);
}
?>