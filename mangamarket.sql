-- ============================================================
--  MangaMarket — Base de données complète
--  MySQL / MariaDB
--
--  Ce fichier contient toute la structure et les données
--  nécessaires pour faire fonctionner le site MangaMarket.
--
--  Pour l'utiliser :
--    1. Ouvrir HeidiSQL
--    2. Fichier → Charger un fichier SQL
--    3. Sélectionner ce fichier et exécuter
--
--  Tables créées :
--    - categories      : types de manga (Shonen, Seinen…)
--    - produits        : les mangas avec stock
--    - utilisateurs    : comptes clients
--    - commandes       : en-têtes de commandes
--    - commande_lignes : détail des articles commandés
--    - messages_contact: messages du formulaire de contact
--    - panier          : panier persistant pour les connectés
-- ============================================================

-- Création de la base si elle n'existe pas encore
-- CHARACTER SET utf8mb4 : support des emojis et caractères japonais
-- COLLATE utf8mb4_unicode_ci : tri insensible à la casse et aux accents
CREATE DATABASE IF NOT EXISTS mangamarket
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Sélection de la base pour les instructions suivantes
USE mangamarket;

-- ────────────────────────────────────────────────────────────
--  TABLE : categories
--  Stocke les genres de manga disponibles sur le site.
--  Utilisée pour filtrer le catalogue et catégoriser les produits.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Identifiant unique auto-incrémenté
    slug  VARCHAR(20)      NOT NULL UNIQUE,          -- Identifiant URL (ex: "shonen") — UNIQUE pour éviter les doublons
    nom   VARCHAR(50)      NOT NULL,                 -- Nom affiché (ex: "Shonen")
    PRIMARY KEY (id)
) ENGINE=InnoDB; -- InnoDB supporte les clés étrangères et les transactions

-- Insertion des 5 catégories manga
-- INSERT IGNORE ignore les doublons (si le slug existe déjà, pas d'erreur)
INSERT IGNORE INTO categories (slug, nom) VALUES
    ('kodomo', 'Kodomo'), -- Manga pour enfants
    ('shonen', 'Shonen'), -- Manga pour adolescents masculins
    ('shojo',  'Shojo'),  -- Manga pour adolescentes
    ('seinen', 'Seinen'), -- Manga pour adultes masculins
    ('josei',  'Josei');  -- Manga pour adultes féminins

-- ────────────────────────────────────────────────────────────
--  TABLE : produits
--  Stocke les mangas vendus sur le site.
--  Chaque ligne représente un tome d'un manga.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS produits (
    id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Identifiant unique
    titre        VARCHAR(150)      NOT NULL,                -- Titre du manga
    auteur       VARCHAR(150)      NOT NULL,                -- Nom de l'auteur
    tome         TINYINT UNSIGNED  NOT NULL DEFAULT 1,      -- Numéro de tome (1 par défaut)
    prix         DECIMAL(6,2)      NOT NULL,                -- Prix en euros (ex: 7.20)
    stock        SMALLINT UNSIGNED NOT NULL DEFAULT 0,      -- Quantité disponible en stock
    categorie_id TINYINT UNSIGNED  NOT NULL,                -- Référence vers categories.id
    image        VARCHAR(255)      NOT NULL,                -- Chemin relatif de l'image de couverture
    description  TEXT,                                      -- Description du manga (peut être NULL)
    created_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date d'ajout automatique
    PRIMARY KEY (id),
    -- Clé étrangère : garantit que categorie_id correspond à une catégorie existante
    CONSTRAINT fk_produit_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE RESTRICT  -- Empêche la suppression d'une catégorie utilisée
        ON UPDATE CASCADE   -- Répercute les modifications d'ID de catégorie
) ENGINE=InnoDB;

-- Insertion des 20 mangas du catalogue
-- Le 5ème champ est le stock initial, le 6ème est l'ID de catégorie
INSERT IGNORE INTO produits (titre, auteur, tome, prix, stock, categorie_id, image, description) VALUES

-- ── Seinen (id=4) ──────────────────────────────────────────
('Berserk', 'Kentaro Miura', 1, 7.20, 10, 4,
 'asset/img/berserk_tome_1_page_de_couverture.jpg',
 'Berserk, créé par Kentaro Miura, suit le destin tragique de Guts, un guerrier solitaire maniant une épée gigantesque dans un monde médiéval sombre et brutal. Marqué par une trahison atroce liée à Griffith, ancien chef de la Troupe du Faucon, il poursuit une quête de vengeance contre les forces démoniaques qui le traquent.'),

-- ── Shonen (id=2) ──────────────────────────────────────────
('Dragon Ball', 'Akira Toriyama', 35, 7.20, 15, 2,
 'asset/img/dragonball_tome_35_page_de_couverture.jpg',
 'Dragon Ball raconte l\'histoire de Son Goku, un jeune garçon à la force extraordinaire, qui part à l\'aventure avec Bulma pour retrouver les sept boules de cristal capables d\'invoquer le dragon sacré Shenron.'),

('One Piece', 'Eiichiro Oda', 35, 7.20, 12, 2,
 'asset/img/one_piece_tome_35_page_de_couverture.jpg',
 'One Piece suit les aventures de Monkey D. Luffy, un jeune pirate au corps élastique qui rêve de devenir le Roi des Pirates. Avec son équipage, les Chapeaux de Paille, il parcourt les mers à la recherche du trésor légendaire appelé le One Piece.'),

-- ── Kodomo (id=1) ──────────────────────────────────────────
('Inazuma Eleven', 'Tenya Yabuno', 1, 7.20, 8, 1,
 'asset/img/inazuma_eleven_tome_1_page_de_couverture.jpg',
 'Inazuma Eleven raconte l\'histoire de Mamoru Endou, un jeune gardien de but passionné de football qui veut sauver le club de son école, Raimon, menacé de dissolution.'),

-- ── Seinen (id=4) ──────────────────────────────────────────
('Vagabond', 'Takehiko Inoue', 1, 7.20, 6, 4,
 'asset/img/vagabond_tome_1_page_de_couverture.jpg',
 'Vagabond suit le parcours de Shinmen Takezō, un jeune guerrier impulsif qui deviendra le légendaire sabreur Miyamoto Musashi.'),

-- ── Josei (id=5) ───────────────────────────────────────────
('And', 'Marie Okazaki', 1, 6.99, 9, 5,
 'asset/img/and_tome_1_page_de_couverture.jpg',
 'Créé par Ai Yazawa, Nana raconte la rencontre de deux jeunes femmes portant le même prénom que tout oppose mais que le destin réunit à Tokyo.'),

-- ── Shonen (id=2) ──────────────────────────────────────────
('Bleach', 'Tite Kubo', 2, 7.80, 14, 2,
 'asset/img/bleach_tome_2_page_de_couverture.jpg',
 'Bleach raconte l\'histoire d\'Ichigo Kurosaki, un adolescent capable de voir les esprits, qui obtient les pouvoirs d\'un Shinigami après avoir rencontré Rukia Kuchiki.'),

('Demon Slayer', 'Koyoharu Gotoge', 1, 7.40, 20, 2,
 'asset/img/demon_slayer_tome_1_page_de_couverture.jpg',
 'Demon Slayer suit l\'histoire de Tanjiro Kamado, un jeune garçon dont la famille est massacrée par des démons, et dont la sœur Nezuko est transformée en démon.'),

-- ── Kodomo (id=1) ──────────────────────────────────────────
('Doraemon', 'Fujiko F. Fujio', 2, 7.80, 11, 1,
 'asset/img/doraemon_tome_1_page_de_couverture.jpg',
 'Doraemon raconte l\'histoire de Nobita, un garçon maladroit et peu sûr de lui, qui reçoit l\'aide d\'un chat robot venu du futur nommé Doraemon.'),

('Dr Slump', 'Akira Toriyama', 1, 7.25, 7, 1,
 'asset/img/dr_slump_tome_1_page_de_couverture.jpg',
 'Dr. Slump raconte les aventures loufoques du professeur Senbei Norimaki, un inventeur génial mais excentrique, et de sa création, la petite androïde surpuissante Aralé.'),

-- ── Shojo (id=3) ───────────────────────────────────────────
('Fruit Basket', 'Natsuki Takaya', 1, 7.50, 5, 3,
 'asset/img/fruit_basket_tome_1_page_de_couverture.jpg',
 'Fruits Basket raconte l\'histoire de Tohru Honda, une lycéenne gentille et optimiste qui se retrouve à vivre avec la mystérieuse famille Soma après la mort de sa mère.'),

-- ── Josei (id=5) ───────────────────────────────────────────
('Les Fleurs Du Passé', 'Haruka Kawachi', 1, 8.60, 4, 5,
 'asset/img/les_fleurs_du_passe_tome_1_page_de_couverture.jpg',
 'Anohana raconte l\'histoire d\'un groupe d\'amis d\'enfance qui se sont éloignés après la mort tragique de leur amie Menma.'),

-- ── Seinen (id=4) ──────────────────────────────────────────
('Monster', 'Naoki Urasawa', 1, 8.50, 8, 4,
 'asset/img/monster_tome_1_page_de_couverture.jpg',
 'Monster suit le docteur Kenzo Tenma, un brillant neurochirurgien qui sauve la vie d\'un jeune garçon au lieu d\'opérer un politicien influent.'),

-- ── Shojo (id=3) ───────────────────────────────────────────
('Nana', 'Ai Yazawa', 1, 8.40, 13, 3,
 'asset/img/nana_tome_1_page_de_couverture.jpg',
 'Nana raconte l\'histoire de deux jeunes femmes portant le même prénom, Nana Komatsu et Nana Osaki, qui se rencontrent par hasard dans un train pour Tokyo.'),

-- ── Josei (id=5) ───────────────────────────────────────────
('Perfect World To Me', 'Rie Aruga', 1, 9.10, 6, 5,
 'asset/img/perfect_world_to_me_tome_1_page_de_couverture.jpg',
 'Perfect World raconte l\'histoire de Tsugumi Kawana, qui retrouve son amour de jeunesse, Itsuki Ayukawa, et découvre qu\'il est désormais en fauteuil roulant après un accident.'),

('Princess Jellyfish', 'Akiko Higashimura', 1, 9.00, 9, 5,
 'asset/img/princess_jellyfish_tome_1_page_de_couverture.jpg',
 'Princess Jellyfish raconte l\'histoire de Tsukimi Kurashita, une jeune femme passionnée par les méduses qui vit dans une résidence réservée aux otakus.'),

-- ── Shojo (id=3) ───────────────────────────────────────────
('Sailor Moon', 'Naoko Takeuchi', 1, 9.50, 18, 3,
 'asset/img/sailor_moon_tome_1_page_de_couverture.jpg',
 'Sailor Moon suit Usagi Tsukino, une collégienne maladroite qui découvre qu\'elle est la réincarnation d\'une guerrière magique destinée à protéger la Terre.'),

('Sakura Chasseuse De Carte', 'CLAMP', 1, 9.60, 16, 3,
 'asset/img/sakura_chasseuse_de_carte_tome_1_page_de_couverture.jpg',
 'Cardcaptor Sakura raconte l\'histoire de Sakura Kinomoto, une élève de primaire qui libère accidentellement des cartes magiques appelées Cartes de Clow.'),

-- ── Seinen (id=4) ──────────────────────────────────────────
('Vinland Saga', 'Makoto Yukimura', 1, 9.30, 7, 4,
 'asset/img/vinland_saga_tome_1_page_de_couverture.jpg',
 'Vinland Saga suit l\'histoire de Thorfinn, un jeune guerrier viking animé par un profond désir de vengeance après la mort de son père.'),

-- ── Kodomo (id=1) ──────────────────────────────────────────
('Yo-kai Watch', 'Noriyuki Konishi', 1, 9.10, 10, 1,
 'asset/img/yo-kai_watch_tome_1_page_de_couverture.jpg',
 'Yo-kai Watch raconte l\'histoire de Nate Adams, un garçon ordinaire qui obtient une montre spéciale lui permettant de voir et d\'invoquer des Yo-kai.');

-- ────────────────────────────────────────────────────────────
--  TABLE : utilisateurs
--  Stocke les comptes clients du site.
--  Les mots de passe sont stockés sous forme de hash bcrypt,
--  jamais en clair.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateurs (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,      -- Identifiant unique
    prenom     VARCHAR(80)   NOT NULL,                     -- Prénom affiché dans le header
    nom        VARCHAR(80)   NOT NULL,                     -- Nom de famille
    email      VARCHAR(180)  NOT NULL UNIQUE,              -- Email de connexion — UNIQUE
    password   VARCHAR(255)  NOT NULL,                     -- Hash bcrypt (généré par password_hash())
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Date d'inscription automatique
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  TABLE : commandes
--  En-tête de chaque commande passée sur le site.
--  Contient les informations de livraison et le statut.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commandes (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    utilisateur_id INT UNSIGNED,              -- NULL si commande passée en tant qu'invité
    prenom         VARCHAR(80)  NOT NULL,     -- Prénom de livraison (snapshot au moment de la commande)
    nom            VARCHAR(80)  NOT NULL,     -- Nom de livraison
    adresse        VARCHAR(255) NOT NULL,     -- Adresse complète
    code_postal    VARCHAR(10)  NOT NULL,     -- Code postal
    ville          VARCHAR(100) NOT NULL,     -- Ville
    total          DECIMAL(8,2) NOT NULL,     -- Total en euros calculé au moment de la commande
    -- Statut avec valeurs prédéfinies (ENUM)
    statut         ENUM('en_attente','payee','expediee','livree','annulee')
                                NOT NULL DEFAULT 'en_attente', -- Statut initial
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    -- Clé étrangère optionnelle (NULL si invité)
    CONSTRAINT fk_commande_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE SET NULL  -- Si l'utilisateur est supprimé, on garde la commande (NULL)
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  TABLE : commande_lignes
--  Détail des articles de chaque commande.
--  Chaque ligne correspond à un produit dans une commande.
--  Le prix est un "snapshot" : il ne change pas si le prix du produit évolue.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commande_lignes (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    commande_id INT UNSIGNED      NOT NULL,        -- Référence vers la commande parente
    produit_id  SMALLINT UNSIGNED NOT NULL,        -- Référence vers le produit commandé
    quantite    TINYINT UNSIGNED  NOT NULL DEFAULT 1, -- Nombre d'exemplaires commandés
    prix_unit   DECIMAL(6,2)      NOT NULL,        -- Prix unitaire au moment de la commande (snapshot)
    PRIMARY KEY (id),
    CONSTRAINT fk_ligne_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE   -- Supprime les lignes si la commande est supprimée
        ON UPDATE CASCADE,
    CONSTRAINT fk_ligne_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE RESTRICT  -- Empêche la suppression d'un produit commandé
        ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  TABLE : messages_contact
--  Stocke les messages envoyés via le formulaire de contact.
--  Le champ "lu" permet de marquer les messages comme lus
--  (utile pour une interface d'administration future).
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS messages_contact (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom        VARCHAR(80)  NOT NULL,      -- Nom de l'expéditeur
    prenom     VARCHAR(80)  NOT NULL,      -- Prénom de l'expéditeur
    telephone  VARCHAR(20),               -- Téléphone (optionnel, peut être NULL)
    email      VARCHAR(180) NOT NULL,     -- Email de réponse
    message    TEXT         NOT NULL,     -- Contenu du message
    lu         TINYINT(1)   NOT NULL DEFAULT 0, -- 0 = non lu, 1 = lu (pour admin futur)
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  TABLE : panier
--  Panier persistant pour les utilisateurs connectés.
--  Le panier des invités est stocké dans le localStorage du navigateur.
--
--  La contrainte UNIQUE (utilisateur_id, produit_id) garantit
--  qu'un même produit n'apparaît qu'une seule fois par utilisateur.
--  Pour augmenter la quantité, on utilise ON DUPLICATE KEY UPDATE.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS panier (
    id             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    utilisateur_id INT UNSIGNED      NOT NULL,        -- Référence vers l'utilisateur connecté
    produit_id     SMALLINT UNSIGNED NOT NULL,        -- Référence vers le produit
    quantite       TINYINT UNSIGNED  NOT NULL DEFAULT 1, -- Quantité (min 1)
    created_at     TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,         -- Date d'ajout
    updated_at     TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP, -- Mis à jour automatiquement
    PRIMARY KEY (id),
    -- Un utilisateur ne peut avoir qu'une seule ligne par produit
    UNIQUE KEY uq_user_produit (utilisateur_id, produit_id),
    CONSTRAINT fk_panier_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE   -- Vide le panier si l'utilisateur est supprimé
        ON UPDATE CASCADE,
    CONSTRAINT fk_panier_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE CASCADE   -- Retire du panier si le produit est supprimé
        ON UPDATE CASCADE
) ENGINE=InnoDB;