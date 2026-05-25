# PhysioActiv By SS — Site web et espace d'administration

Site web complet développé pour le centre de kinésithérapie **PhysioActiv By SS** (Soualem, Casablanca). Le projet associe un **site vitrine public** présentant le centre et ses services, et un **espace d'administration privé** permettant de gérer les demandes des patients reçues via le formulaire de contact.

> Projet réalisé par **Yassine Temsamani** — ISGA Casablanca, Cycle Ingénieur (CI ISI), 2025–2026.

---

## Fonctionnalités

### Site vitrine (public)
- Pages : accueil, services, à propos, galerie, témoignages, contact
- Design moderne et responsive (ordinateur et mobile)
- Carrousels pour les services et les témoignages
- Galerie avec visionneuse interactive (lightbox : navigation flèches / clavier)
- Formulaire de contact avec validation, enregistrement en base et envoi d'un email de notification

### Espace d'administration (privé)
- Authentification par session
- Tableau de bord avec statistiques (total, non lus, lus, du jour)
- Graphique d'activité des 30 derniers jours (Chart.js)
- Consultation du détail d'un message (fenêtre modale)
- Réponse rapide par email, WhatsApp ou téléphone
- Corbeille avec suppression logique (soft delete) et restauration
- Sélection multiple et actions groupées
- Recherche instantanée (AJAX, sans rechargement)
- Pagination (20 messages par page)
- Notes internes par message
- Notifications en temps réel (signal sonore + alerte visuelle)
- Export des demandes vers un fichier tableur (CSV / Excel)

---

## Technologies utilisées

| Catégorie | Outils |
|-----------|--------|
| Front-end | HTML5, CSS3, JavaScript (AJAX / fetch) |
| Back-end | PHP |
| Base de données | MySQL |
| Bibliothèques | Chart.js, PHPMailer |
| Environnement | XAMPP (Apache + MySQL) |

---

## Installation et lancement (en local)

Le projet a été développé et testé en local avec **XAMPP**.

### Prérequis
- [XAMPP](https://www.apachefriends.org) installé (Apache + MySQL)

### Étapes

1. **Récupérer le projet**
   Télécharger ce dépôt (bouton `Code` → `Download ZIP`) ou le cloner.

2. **Placer les fichiers**
   Copier le dossier du projet dans le répertoire `htdocs` de XAMPP :
   ```
   C:\xampp\htdocs\physioactiv\
   ```

3. **Démarrer les services**
   Ouvrir le **XAMPP Control Panel** et démarrer **Apache** et **MySQL**.

4. **Créer la base de données**
   - Ouvrir phpMyAdmin : `http://localhost/phpmyadmin/`
   - Créer une base nommée **`cabinet_kine`**
   - Importer le fichier SQL fourni (`cabinet_kine.sql`) via l'onglet **Importer**

5. **Vérifier la connexion**
   Le fichier `connexion.php` contient les paramètres de connexion à la base. Adapter le port si nécessaire (par défaut MySQL : `3306`, ou `3307` selon la configuration).

6. **Lancer l'application**
   - Site vitrine : `http://localhost/physioactiv/index.html`
   - Espace d'administration : `http://localhost/physioactiv/login.php`

---

## Accès à l'espace d'administration

| Champ | Valeur |
|-------|--------|
| Identifiant | `admin` |
| Mot de passe | `kine1234` |

---

## Structure du projet

```
physioactiv/
├── index.html              # Site vitrine
├── style.css               # Styles du site
├── script.js               # Interactivité (carrousels, lightbox, validation)
├── traitement.php          # Traitement du formulaire de contact
├── connexion.php           # Connexion à la base de données
├── login.php               # Page de connexion admin
├── admin.php               # Tableau de bord et gestion des messages
├── logout.php              # Déconnexion
├── enregistrer_note.php    # Enregistrement des notes internes (AJAX)
├── check_messages.php      # Vérification des nouveaux messages (AJAX)
├── recherche.php           # Recherche instantanée (AJAX)
├── ligne_message.php       # Fonction partagée de génération des lignes
├── export.php              # Export des données (CSV / Excel)
├── cabinet_kine.sql        # Export de la base de données
└── PHPMailer/              # Bibliothèque d'envoi d'emails
```

---

## Remarque

Ce projet est un **prototype fonctionnel** développé dans un cadre pédagogique. Il est destiné à être exécuté en environnement local. Un déploiement en production nécessiterait des étapes complémentaires (hébergement, nom de domaine, certificat HTTPS, renforcement de la sécurité de l'authentification).
