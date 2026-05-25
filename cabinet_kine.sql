-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 25, 2026 at 03:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cabinet_kine`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0,
  `supprime` tinyint(1) NOT NULL DEFAULT 0,
  `date_suppression` datetime DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `nom`, `telephone`, `email`, `message`, `date_envoi`, `lu`, `supprime`, `date_suppression`, `note`) VALUES
(1, 'Fatima Zahra Idrissi', '0612345678', 'fatima.idrissi@gmail.com', 'Bonjour, je souffre de lombalgie chronique depuis plusieurs mois. Pourriez-vous me proposer un rendez-vous cette semaine ?', '2026-05-14 23:07:27', 1, 0, NULL, NULL),
(2, 'Mohammed Benjelloun', '0661234567', 'm.benjelloun@hotmail.com', 'Bonjour, je suis joueur de rugby et j\'ai une forte douleur à l\'épaule droite depuis mon dernier match. J\'aurais besoin d\'une séance rapidement.', '2026-05-14 21:37:27', 1, 0, NULL, NULL),
(3, 'Aicha Bennani', '0623456789', 'aicha.bennani@gmail.com', 'Bonjour, je viens d\'accoucher et mon médecin m\'a recommandé une rééducation périnéale. Je souhaite prendre rendez-vous.', '2026-05-14 19:37:27', 1, 0, NULL, NULL),
(4, 'Karim Tazi', '0670123456', 'karim.tazi@yahoo.fr', 'Bonjour Mme Semlali, j\'ai des douleurs de sciatique au niveau de la jambe gauche. Avez-vous des disponibilités cette semaine ?', '2026-05-14 17:37:27', 1, 0, NULL, NULL),
(5, 'Yasmine Lahlou', '0634567890', 'y.lahlou@gmail.com', 'Bonjour, j\'ai un torticolis depuis 3 jours qui ne passe pas malgré les anti-inflammatoires. Pouvez-vous me recevoir ?', '2026-05-14 15:37:27', 1, 0, NULL, NULL),
(6, 'Mehdi Cherkaoui', '0698765432', 'mehdi.cherkaoui@gmail.com', 'Bonjour, j\'ai développé une tendinite type \"tennis elbow\" au coude droit. Je joue beaucoup au tennis et j\'ai besoin de séances de kiné.', '2026-05-13 23:37:27', 1, 0, NULL, NULL),
(7, 'Sara Bouzid', '0645678901', 'sara.bouzid@hotmail.com', 'Bonjour, je cherche un suivi kiné pour mon dos après ma grossesse. Quels sont vos horaires disponibles ?', '2026-05-13 20:37:27', 1, 0, NULL, NULL),
(8, 'Youssef Naciri', '0687654321', 'y.naciri@gmail.com', 'Bonjour, je sors d\'une opération du ménisque et mon chirurgien m\'a prescrit 20 séances de rééducation. Je voudrais commencer dès que possible.', '2026-05-13 18:37:27', 1, 0, NULL, NULL),
(9, 'Hassan El Fassi', '0656789012', 'h.elfassi@gmail.com', 'Bonjour, je suis âgé de 72 ans et j\'ai des problèmes d\'équilibre suite à plusieurs chutes. Mon médecin m\'a conseillé un kiné spécialisé.', '2026-05-10 23:37:27', 1, 0, NULL, NULL),
(10, 'Nadia Berrada', '0676543210', 'nadia.berrada@gmail.com', 'Bonjour, je travaille de longues heures devant un ordinateur et j\'ai des cervicalgies chroniques. Pouvez-vous me proposer un suivi ?', '2026-05-10 21:37:27', 1, 0, NULL, NULL),
(11, 'Hicham Alaoui', '0667890123', 'hicham.alaoui@gmail.com', 'Bonjour, j\'ai eu un claquage à la cuisse pendant une séance de footing. Je voudrais une consultation pour évaluer la blessure.', '2026-05-10 19:37:27', 1, 0, NULL, NULL),
(12, 'Khadija Sebbar', '0665432109', 'k.sebbar@hotmail.com', 'Bonjour, on m\'a diagnostiqué une hernie discale L4-L5. Mon médecin m\'a orientée vers vous. Disponible cette semaine ?', '2026-05-10 16:37:27', 1, 0, NULL, NULL),
(13, 'Soufiane Bennis', '0678901234', 'soufiane.bennis@gmail.com', 'Bonjour, j\'ai une capsulite rétractile de l\'épaule droite. Avez-vous l\'habitude de traiter cette pathologie ?', '2026-05-09 23:37:27', 1, 0, NULL, NULL),
(14, 'Lina Amrani', '0654321098', 'lina.amrani@gmail.com', 'Bonjour, je vous contacte pour ma fille de 14 ans qui a une scoliose. Le médecin recommande un suivi kiné régulier.', '2026-05-07 23:37:27', 1, 0, NULL, NULL),
(15, 'Rachid Bensaid', '0689012345', 'r.bensaid@gmail.com', 'Bonjour, ma sciatique est revenue après quelques mois sans douleur. J\'aurais besoin de nouvelles séances.', '2026-05-07 20:37:27', 1, 0, NULL, NULL),
(16, 'Samira Ziani', '0643210987', 'samira.ziani@hotmail.com', 'Bonjour, j\'ai des fourmillements dans la main droite, mon médecin pense à un syndrome du canal carpien. Que proposez-vous ?', '2026-05-04 23:37:27', 1, 0, NULL, NULL),
(17, 'Omar Mansouri', '0690123456', 'omar.mansouri@gmail.com', 'Bonjour, mon père a fait un AVC il y a 3 mois et nous cherchons un kiné pour sa rééducation. Acceptez-vous les visites à domicile ?', '2026-05-02 23:37:27', 1, 0, NULL, NULL),
(18, 'Inès El Khattabi', '0632109876', 'ines.elkhattabi@gmail.com', 'Bonjour, je me suis fait une entorse de la cheville droite en faisant du sport. Je voudrais commencer la rééducation.', '2026-04-30 23:37:27', 1, 0, NULL, NULL),
(19, 'Adam Sefrioui', '0691234567', 'adam.sefrioui@gmail.com', 'Bonjour, je suis ingénieur et j\'ai des douleurs cervicales très importantes liées à ma posture de bureau. Conseils + séances ?', '2026-04-29 23:37:27', 1, 0, NULL, NULL),
(20, 'Léa Saidi', '0621098765', 'lea.saidi@hotmail.com', 'Bonjour, je viens de me faire opérer de l\'épaule (coiffe des rotateurs) et j\'ai besoin de séances de rééducation post-opératoire.', '2026-04-28 23:37:27', 1, 0, NULL, NULL),
(21, 'Anissa El Maleh', '0692345678', 'anissa.elmaleh@gmail.com', 'Bonjour, j\'ai un blocage lombaire depuis ce matin, je n\'arrive presque plus à bouger. Y a-t-il une urgence possible ?', '2026-04-26 23:37:27', 1, 0, NULL, NULL),
(22, 'Jad Lazaar', '0610987654', 'jad.lazaar@gmail.com', 'Bonjour, je suis asthmatique et mon pneumologue m\'a recommandé de la kinésithérapie respiratoire. Vous pratiquez ?', '2026-04-26 19:37:27', 1, 0, NULL, NULL),
(23, 'Sofia Belhaj', '0693456789', 'sofia.belhaj@hotmail.com', 'Bonjour, je suis coureuse de fond et j\'ai une douleur récurrente au genou. Je cherche un kiné qui connaît les pathologies sportives.', '2026-04-26 16:37:27', 1, 0, NULL, NULL),
(24, 'Othmane Bouazza', '0609876543', 'o.bouazza@gmail.com', 'Bonjour, mon ostéopathe m\'a orienté vers vous pour une hernie discale lombaire. Vos honoraires s\'il vous plaît ?', '2026-04-23 23:37:27', 1, 0, NULL, NULL),
(25, 'Imane Berrechid', '0694567890', 'imane.berrechid@gmail.com', 'Bonjour, j\'ai accouché il y a 2 mois et je cherche une rééducation périnée et abdominale.', '2026-04-20 23:37:27', 1, 0, NULL, NULL),
(26, 'Younes Khalil', '0608765432', 'younes.khalil@gmail.com', 'Bonjour, je prépare un marathon et j\'ai une tendinite d\'Achille. Je voudrais un suivi régulier.', '2026-04-19 23:37:27', 1, 0, NULL, NULL),
(27, 'Houda Sefrouny', '0695678901', 'houda.sefrouny@hotmail.com', 'Bonjour, j\'ai déclaré une paralysie faciale (paralysie de Bell) il y a une semaine. Le médecin m\'a parlé de kiné. Que proposez-vous ?', '2026-04-18 23:37:27', 1, 0, NULL, NULL),
(28, 'Driss Aboulaazm', '0607654321', 'd.aboulaazm@gmail.com', 'Bonjour, j\'ai des douleurs articulaires depuis plusieurs années. Mon rhumatologue m\'a parlé d\'un suivi kiné. C\'est possible chez vous ?', '2026-04-17 23:37:27', 1, 0, NULL, NULL),
(29, 'Salma Filali', '0696789012', 'salma.filali@gmail.com', 'Bonjour, je travaille beaucoup à l\'ordinateur et j\'ai un mal de dos qui devient chronique. Bilan possible ?', '2026-04-16 23:37:27', 1, 0, NULL, NULL),
(30, 'Reda Sbihi', '0606543210', 'reda.sbihi@gmail.com', 'Bonjour, je viens d\'être déplâtré suite à une fracture du poignet. Mon médecin recommande 15 séances de rééducation.', '2026-04-16 19:37:27', 1, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
