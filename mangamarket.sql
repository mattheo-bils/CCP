-- ============================================================
--  MangaMarket — Base de données
--  MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS mangamarket
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mangamarket;

-- ────────────────────────────────────────────────────────────
--  Catégories manga
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id    TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug  VARCHAR(20)      NOT NULL UNIQUE,
    nom   VARCHAR(50)      NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (slug, nom) VALUES
    ('kodomo', 'Kodomo'),
    ('shonen', 'Shonen'),
    ('shojo',  'Shojo'),
    ('seinen', 'Seinen'),
    ('josei',  'Josei');

-- ────────────────────────────────────────────────────────────
--  Produits (mangas)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS produits (
    id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    titre        VARCHAR(150)      NOT NULL,
    auteur       VARCHAR(150)      NOT NULL,
    tome         TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    prix         DECIMAL(6,2)      NOT NULL,
    categorie_id TINYINT UNSIGNED  NOT NULL,
    image        VARCHAR(255)      NOT NULL,
    description  TEXT,
    created_at   TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_produit_categorie
        FOREIGN KEY (categorie_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO produits (titre, auteur, tome, prix, categorie_id, image, description) VALUES
('Berserk',                'Kentaro Miura',      1,  7.20, 4, 'asset/img/berserk_tome_1_page_de_couverture.jpg',
 'Berserk, créé par Kentaro Miura, suit le destin tragique de Guts, un guerrier solitaire maniant une épée gigantesque dans un monde médiéval sombre et brutal. Marqué par une trahison atroce liée à Griffith, ancien chef de la Troupe du Faucon, il poursuit une quête de vengeance contre les forces démoniaques qui le traquent.'),

('Dragon Ball',            'Akira Toriyama',    35,  7.20, 2, 'asset/img/dragonball_tome_35_page_de_couverture.jpg',
 'Dragon Ball raconte l\'histoire de Son Goku, un jeune garçon à la force extraordinaire, qui part à l\'aventure avec Bulma pour retrouver les sept boules de cristal capables d\'invoquer le dragon sacré Shenron.'),

('One Piece',              'Eiichiro Oda',       35,  7.20, 2, 'asset/img/one_piece_tome_35_page_de_couverture.jpg',
 'One Piece suit les aventures de Monkey D. Luffy, un jeune pirate au corps élastique qui rêve de devenir le Roi des Pirates. Avec son équipage, les Chapeaux de Paille, il parcourt les mers à la recherche du trésor légendaire appelé le One Piece.'),

('Inazuma Eleven',         'Tenya Yabuno',        1,  7.20, 1, 'asset/img/inazuma_eleven_tome_1_page_de_couverture.jpg',
 'Inazuma Eleven raconte l\'histoire de Mamoru Endou, un jeune gardien de but passionné de football qui veut sauver le club de son école, Raimon, menacé de dissolution.'),

('Vagabond',               'Takehiko Inoue',      1,  7.20, 4, 'asset/img/vagabond_tome_1_page_de_couverture.jpg',
 'Vagabond suit le parcours de Shinmen Takezō, un jeune guerrier impulsif qui deviendra le légendaire sabreur Miyamoto Musashi.'),

('And',                    'Marie Okazaki',       1,  6.99, 5, 'asset/img/and_tome_1_page_de_couverture.jpg',
 'Créé par Ai Yazawa, Nana raconte la rencontre de deux jeunes femmes portant le même prénom que tout oppose mais que le destin réunit à Tokyo.'),

('Bleach',                 'Tite Kubo',           2,  7.80, 2, 'asset/img/bleach_tome_2_page_de_couverture.jpg',
 'Bleach raconte l\'histoire d\'Ichigo Kurosaki, un adolescent capable de voir les esprits, qui obtient les pouvoirs d\'un Shinigami après avoir rencontré Rukia Kuchiki.'),

('Demon Slayer',           'Koyoharu Gotoge',     1,  7.40, 2, 'asset/img/demon_slayer_tome_1_page_de_couverture.jpg',
 'Demon Slayer suit l\'histoire de Tanjiro Kamado, un jeune garçon dont la famille est massacrée par des démons, et dont la sœur Nezuko est transformée en démon.'),

('Doraemon',               'Fujiko F. Fujio',     2,  7.80, 1, 'asset/img/doraemon_tome_1_page_de_couverture.jpg',
 'Doraemon raconte l\'histoire de Nobita, un garçon maladroit et peu sûr de lui, qui reçoit l\'aide d\'un chat robot venu du futur nommé Doraemon.'),

('Dr Slump',               'Akira Toriyama',      1,  7.25, 1, 'asset/img/dr_slump_tome_1_page_de_couverture.jpg',
 'Dr. Slump raconte les aventures loufoques du professeur Senbei Norimaki, un inventeur génial mais excentrique, et de sa création, la petite androïde surpuissante Aralé.'),

('Fruit Basket',           'Natsuki Takaya',      1,  7.50, 3, 'asset/img/fruit_basket_tome_1_page_de_couverture.jpg',
 'Fruits Basket raconte l\'histoire de Tohru Honda, une lycéenne gentille et optimiste qui se retrouve à vivre avec la mystérieuse famille Soma après la mort de sa mère.'),

('Les Fleurs Du Passé',    'Haruka Kawachi',      1,  8.60, 5, 'asset/img/les_fleurs_du_passe_tome_1_page_de_couverture.jpg',
 'Anohana raconte l\'histoire d\'un groupe d\'amis d\'enfance qui se sont éloignés après la mort tragique de leur amie Menma.'),

('Monster',                'Naoki Urasawa',       1,  8.50, 4, 'asset/img/monster_tome_1_page_de_couverture.jpg',
 'Monster suit le docteur Kenzo Tenma, un brillant neurochirurgien qui sauve la vie d\'un jeune garçon au lieu d\'opérer un politicien influent.'),

('Nana',                   'Ai Yazawa',           1,  8.40, 3, 'asset/img/nana_tome_1_page_de_couverture.jpg',
 'Nana raconte l\'histoire de deux jeunes femmes portant le même prénom, Nana Komatsu et Nana Osaki, qui se rencontrent par hasard dans un train pour Tokyo.'),

('Perfect World To Me',    'Rie Aruga',           1,  9.10, 5, 'asset/img/perfect_world_to_me_tome_1_page_de_couverture.jpg',
 'Perfect World raconte l\'histoire de Tsugumi Kawana, qui retrouve son amour de jeunesse, Itsuki Ayukawa, et découvre qu\'il est désormais en fauteuil roulant après un accident.'),

('Princess Jellyfish',     'Akiko Higashimura',   1,  9.00, 5, 'asset/img/princess_jellyfish_tome_1_page_de_couverture.jpg',
 'Princess Jellyfish raconte l\'histoire de Tsukimi Kurashita, une jeune femme passionnée par les méduses qui vit dans une résidence réservée aux otakus.'),

('Sailor Moon',            'Naoko Takeuchi',      1,  9.50, 3, 'asset/img/sailor_moon_tome_1_page_de_couverture.jpg',
 'Sailor Moon suit Usagi Tsukino, une collégienne maladroite qui découvre qu\'elle est la réincarnation d\'une guerrière magique destinée à protéger la Terre.'),

('Sakura Chasseuse De Carte', 'CLAMP',            1,  9.60, 3, 'asset/img/sakura_chasseuse_de_carte_tome_1_page_de_couverture.jpg',
 'Cardcaptor Sakura raconte l\'histoire de Sakura Kinomoto, une élève de primaire qui libère accidentellement des cartes magiques appelées Cartes de Clow.'),

('Vinland Saga',           'Makoto Yukimura',     1,  9.30, 4, 'asset/img/vinland_saga_tome_1_page_de_couverture.jpg',
 'Vinland Saga suit l\'histoire de Thorfinn, un jeune guerrier viking animé par un profond désir de vengeance après la mort de son père.'),

('Yo-kai Watch',           'Noriyuki Konishi',    1,  9.10, 1, 'asset/img/yo-kai_watch_tome_1_page_de_couverture.jpg',
 'Yo-kai Watch raconte l\'histoire de Nate Adams, un garçon ordinaire qui obtient une montre spéciale lui permettant de voir et d\'invoquer des Yo-kai.');

-- ────────────────────────────────────────────────────────────
--  Utilisateurs
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS utilisateurs (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    prenom     VARCHAR(80)   NOT NULL,
    nom        VARCHAR(80)   NOT NULL,
    email      VARCHAR(180)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,   -- bcrypt hash
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  Commandes
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commandes (
    id             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    utilisateur_id INT UNSIGNED,                           -- NULL si invité
    prenom         VARCHAR(80)     NOT NULL,
    nom            VARCHAR(80)     NOT NULL,
    adresse        VARCHAR(255)    NOT NULL,
    code_postal    VARCHAR(10)     NOT NULL,
    ville          VARCHAR(100)    NOT NULL,
    total          DECIMAL(8,2)    NOT NULL,
    statut         ENUM('en_attente','payee','expediee','livree','annulee')
                                   NOT NULL DEFAULT 'en_attente',
    created_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_commande_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  Lignes de commande
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS commande_lignes (
    id          INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    commande_id INT UNSIGNED      NOT NULL,
    produit_id  SMALLINT UNSIGNED NOT NULL,
    quantite    TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    prix_unit   DECIMAL(6,2)      NOT NULL,   -- snapshot du prix au moment de l'achat
    PRIMARY KEY (id),
    CONSTRAINT fk_ligne_commande
        FOREIGN KEY (commande_id) REFERENCES commandes(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ligne_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
--  Messages de contact
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS messages_contact (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom        VARCHAR(80)  NOT NULL,
    prenom     VARCHAR(80)  NOT NULL,
    telephone  VARCHAR(20),
    email      VARCHAR(180) NOT NULL,
    message    TEXT         NOT NULL,
    lu         TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
-- ────────────────────────────────────────────────────────────
--  Panier (articles en attente de commande)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS panier (
    id             INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    utilisateur_id INT UNSIGNED      NOT NULL,
    produit_id     SMALLINT UNSIGNED NOT NULL,
    quantite       TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    created_at     TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_produit (utilisateur_id, produit_id),
    CONSTRAINT fk_panier_utilisateur
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_panier_produit
        FOREIGN KEY (produit_id) REFERENCES produits(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;