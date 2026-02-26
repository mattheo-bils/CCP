const produits = [
    {
        id: 1,
        titre: "Berserk",
        auteur: "",
        tome: 1,
        prix: 7.20,
        categorie: "seinen",
        image: "asset/img/berserk_tome_1_page_de_couverture.jpg",
        description: "Berserk, créé par Kentaro Miura, suit le destin tragique de Guts, un guerrier solitaire maniant une épée gigantesque dans un monde médiéval sombre et brutal. Marqué par une trahison atroce liée à Griffith, ancien chef de la Troupe du Faucon, il poursuit une quête de vengeance contre les forces démoniaques qui le traquent. Entre violence, désespoir et lutte acharnée contre le destin, Berserk explore la noirceur de l’âme humaine et la volonté de survivre malgré tout."
    },
    {
        id: 2,
        titre: "Dragon Ball",
        auteur: "",
        tome: 35,
        prix: 7.20,
        categorie: "shonen",
        image: "asset/img/dragonball_tome_35_page_de_couverture.jpg",
        description: "Dragon Ball raconte l’histoire de Son Goku, un jeune garçon à la force extraordinaire, qui part à l’aventure avec Bulma pour retrouver les sept boules de cristal capables d’invoquer le dragon sacré Shenron. Au cours de leur voyage, ils affrontent divers ennemis et se lient d’amitié avec d’autres combattants hauts en couleur. Cette quête marque le début d’une grande aventure mêlant humour, arts martiaux et combats légendaires."
    },
    {
        id: 3,
        titre: "One Piece",
        auteur: "",
        tome: 35,
        prix: 7.20,
        categorie: "shonen",
        image: "asset/img/one_piece_tome_35_page_de_couverture.jpg",
        description: "One Piece suit les aventures de Monkey D. Luffy, un jeune pirate au corps élastique qui rêve de devenir le Roi des Pirates. Avec son équipage, les Chapeaux de Paille, il parcourt les mers à la recherche du trésor légendaire appelé le One Piece. Au fil de leur voyage, ils affrontent de puissants ennemis, se font des alliés et découvrent les mystères du Gouvernement Mondial et du Siècle Oublié."
    },
    {
        id: 4,
        titre: "Inazuma Eleven",
        auteur: "",
        tome: 1,
        prix: 7.20,
        categorie: "kodomo",
        image: "asset/img/inazuma_eleven_tome_1_page_de_couverture.jpg",
        description: "Inazuma Eleven raconte l’histoire de Mamoru Endou, un jeune gardien de but passionné de football qui veut sauver le club de son école, Raimon, menacé de dissolution. Avec ses amis, il affronte des équipes redoutables en utilisant des techniques spéciales spectaculaires. Au fil des tournois, l’équipe gagne en force, en amitié et découvre que le football est avant tout une question de courage et d’esprit d’équipe."
    },
    {
        id: 5,
        titre: "Vagabond",
        auteur: "",
        tome: 1,
        prix: 7.20,
        categorie: "seinen",
        image: "asset/img/vagabond_tome_1_page_de_couverture.jpg",
        description: "Vagabond suit le parcours de Shinmen Takezō, un jeune guerrier impulsif qui deviendra le légendaire sabreur Miyamoto Musashi. Après la bataille de Sekigahara, il entame une quête spirituelle et martiale pour devenir invincible sous le ciel. Au fil de ses duels et de ses rencontres, il apprend que la véritable force ne réside pas seulement dans le sabre, mais aussi dans la maîtrise de soi et la compréhension de la vie."
    },
    {
        id: 6,
        titre: "And",
        auteur: "",
        tome: 1,
        prix: 6.99,
        categorie: "josei",
        image: "asset/img/and_tome_1_page_de_couverture.jpg",
        description: "Créé par Ai Yazawa, Nana raconte la rencontre de deux jeunes femmes portant le même prénom, Nana Osaki et Nana Komatsu, que tout oppose mais que le destin réunit à Tokyo. L’une rêve de percer dans le monde du rock, tandis que l’autre cherche avant tout l’amour et une vie stable. Entre ambitions, amitiés passionnelles et relations amoureuses complexes, le manga explore avec réalisme les espoirs et les désillusions de l’âge adulte."
    },
    {
        id: 7,
        titre: "Bleach",
        auteur: "",
        tome: 2,
        prix: 7.80,
        categorie: "shonen",
        image: "asset/img/bleach_tome_2_page_de_couverture.jpg",
        description: "Bleach raconte l’histoire d’Ichigo Kurosaki, un adolescent capable de voir les esprits, qui obtient les pouvoirs d’un Shinigami après avoir rencontré Rukia Kuchiki. Chargé de protéger les humains des Hollows, des esprits maléfiques, il combat de puissants ennemis tout en découvrant le monde de la Soul Society. Au fil des batailles, Ichigo dévoile des pouvoirs cachés et se retrouve au cœur de conflits majeurs entre Shinigamis, Arrancars et Quincy."
    },
    {
        id: 8,
        titre: "Demon Slayer",
        auteur: "",
        tome: 1,
        prix: 7.40,
        categorie: "shonen",
        image: "asset/img/demon_slayer_tome_1_page_de_couverture.jpg",
        description: "Demon Slayer: Kimetsu no Yaiba suit l’histoire de Tanjiro Kamado, un jeune garçon dont la famille est massacrée par des démons, et dont la sœur Nezuko est transformée en démon. Déterminé à la sauver et à venger les siens, Tanjiro rejoint les pourfendeurs de démons et apprend des techniques de respiration pour combattre ces créatures. Au fil de son voyage, il affronte de redoutables ennemis et se rapproche de la vérité sur l’origine des démons."
    },
    {
        id: 9,
        titre: "Doraemon",
        auteur: "",
        tome: 2,
        prix: 7.80,
        categorie: "kodomo",
        image: "asset/img/doraemon_tome_1_page_de_couverture.jpg",
        description: "Doraemon raconte l’histoire de Nobita, un garçon maladroit et peu sûr de lui, qui reçoit l’aide d’un chat robot venu du futur nommé Doraemon. Envoyé par le descendant de Nobita pour améliorer son avenir, Doraemon utilise des gadgets futuristes sortis de sa poche magique pour résoudre ses problèmes quotidiens. Mais malgré ces inventions extraordinaires, Nobita apprend peu à peu que le courage, l’amitié et les efforts personnels sont les véritables clés pour changer son destin."
    },
    {
        id: 10,
        titre: "Dr Slump",
        auteur: "",
        tome: 1,
        prix: 7.25,
        categorie: "kodomo",
        image: "asset/img/dr_slump_tome_1_page_de_couverture.jpg",
        description: "Dr. Slump raconte les aventures loufoques du professeur Senbei Norimaki, un inventeur génial mais excentrique, et de sa création, la petite androïde surpuissante Aralé. Dans le village farfelu de Penguin Village, Aralé provoque des situations absurdes et comiques grâce à sa force incroyable et sa naïveté. La série mêle humour déjanté, parodies et gags visuels dans un univers totalement décalé imaginé par Akira Toriyama."
    },
    {
        id: 11,
        titre: "Fruit Basket",
        auteur: "",
        tome: 1,
        prix: 7.50,
        categorie: "shojo",
        image: "asset/img/fruit_basket_tome_1_page_de_couverture.jpg",
        description: "Fruits Basket raconte l’histoire de Tohru Honda, une lycéenne gentille et optimiste qui se retrouve à vivre avec la mystérieuse famille Soma après la mort de sa mère. Elle découvre que certains membres de la famille sont victimes d’une malédiction qui les transforme en animaux du zodiaque chinois lorsqu’ils sont enlacés par une personne du sexe opposé. Au fil du temps, Tohru les aide à guérir leurs blessures émotionnelles tout en apprenant elle-même la valeur de l’amour, de l’acceptation et de la famille"
    },
    {
        id: 12,
        titre: "Les Fleurs Du Passé",
        auteur: "",
        tome: 1,
        prix: 8.60,
        categorie: "josei",
        image: "asset/img/les_fleurs_du_passe_tome_1_page_de_couverture.jpg",
        description: "Anohana: The Flower We Saw That Day raconte l’histoire d’un groupe d’amis d’enfance qui se sont éloignés après la mort tragique de leur amie Menma. Des années plus tard, son esprit apparaît à Jinta, leur ancien leader, pour lui demander d’exaucer un vœu oublié. En se réunissant pour l’aider à trouver la paix, les amis affrontent leurs regrets, leur culpabilité et les blessures du passé."
    },
    {
        id: 13,
        titre: "Monster",
        auteur: "",
        tome: 1,
        prix: 8.50,
        categorie: "seinen",
        image: "asset/img/monster_tome_1_page_de_couverture.jpg",
        description: "Monster suit le docteur Kenzo Tenma, un brillant neurochirurgien qui sauve la vie d’un jeune garçon au lieu d’opérer un politicien influent. Des années plus tard, il découvre que l’enfant qu’il a sauvé, Johan Liebert, est devenu un dangereux tueur manipulateur. Hanté par sa décision, Tenma entreprend un voyage à travers l’Europe pour arrêter celui qu’il considère comme le “monstre” qu’il a lui-même créé."
    },
    {
        id: 14,
        titre: "Nana",
        auteur: "",
        tome: 1,
        prix: 8.40,
        categorie: "shojo",
        image: "asset/img/nana_tome_1_page_de_couverture.jpg",
        description: "Nana raconte l’histoire de deux jeunes femmes portant le même prénom, Nana Komatsu et Nana Osaki, qui se rencontrent par hasard dans un train pour Tokyo. Malgré leurs personnalités opposées — l’une rêve d’amour, l’autre de succès musical — elles deviennent colocataires et développent une amitié profonde. Entre relations amoureuses, ambitions artistiques et épreuves de la vie adulte, la série explore les thèmes de l’amour, de la dépendance affective et des choix de vie."
    },
    {
        id: 15,
        titre: "Perfect World To Me",
        auteur: "",
        tome: 1,
        prix: 9.10,
        categorie: "josei",
        image: "asset/img/perfect_world_to_me_tome_1_page_de_couverture.jpg",
        description: "Perfect World raconte l’histoire de Tsugumi Kawana, qui retrouve son amour de jeunesse, Itsuki Ayukawa, et découvre qu’il est désormais en fauteuil roulant après un accident. Malgré les difficultés liées au handicap et au regard de la société, Tsugumi décide de poursuivre ses sentiments et d’affronter les obstacles à ses côtés. L’œuvre aborde avec réalisme l’amour, l’acceptation, les défis du quotidien et la force nécessaire pour construire une relation au-delà des préjugés."
    },
    {
        id: 16,
        titre: "Princess Jellyfish",
        auteur: "",
        tome: 1,
        prix: 9.00,
        categorie: "josei",
        image: "asset/img/princess_jellyfish_tome_1_page_de_couverture.jpg",
        description: "Princess Jellyfish raconte l’histoire de Tsukimi Kurashita, une jeune femme passionnée par les méduses qui vit dans une résidence réservée aux otakus. Sa vie change lorsqu’elle rencontre Kuranosuke, un jeune homme élégant qui adore se travestir et qui l’aide, ainsi que ses amies, à gagner en confiance. Entre mode, amitié et rêves personnels, la série explore l’acceptation de soi et le courage d’affirmer sa véritable identité."
    },
    {
        id: 17,
        titre: "Sailor Moon",
        auteur: "",
        tome: 1,
        prix: 9.50,
        categorie: "shojo",
        image: "asset/img/sailor_moon_tome_1_page_de_couverture.jpg",
        description: "Sailor Moon suit Usagi Tsukino, une collégienne maladroite qui découvre qu’elle est la réincarnation d’une guerrière magique destinée à protéger la Terre. Avec l’aide des autres Sailor Guardians, elle combat les forces du mal tout en recherchant la princesse de la Lune et le Cristal d’Argent. Au fil des batailles, Usagi apprend à devenir plus courageuse et à croire en la force de l’amour et de l’amitié."
    },
    {
        id: 18,
        titre: "Sakura Chasseuse De Carte",
        auteur: "",
        tome: 1,
        prix: 9.60,
        categorie: "shojo",
        image: "asset/img/sakura_chasseuse_de_carte_tome_1_page_de_couverture.jpg",
        description: "Cardcaptor Sakura raconte l’histoire de Sakura Kinomoto, une élève de primaire qui libère accidentellement des cartes magiques appelées Cartes de Clow. Aidée par Kero, le gardien des cartes, elle devient “chasseuse de cartes” et doit les récupérer avant qu’elles ne causent des catastrophes. Au fil de ses aventures, Sakura grandit, découvre la magie, l’amitié et les premiers sentiments amoureux."
    },
    {
        id: 19,
        titre: "Vinland Saga",
        auteur: "",
        tome: 1,
        prix: 9.30,
        categorie: "seinen",
        image: "asset/img/vinland_saga_tome_1_page_de_couverture.jpg",
        description: "Vinland Saga suit l’histoire de Thorfinn, un jeune guerrier viking animé par un profond désir de vengeance après la mort de son père. Engagé aux côtés du mercenaire Askeladd, il participe aux guerres sanglantes en Europe tout en cherchant l’occasion de le défier en duel. Au fil du récit, l’histoire dépasse la simple vengeance pour explorer la violence, la quête de sens et le rêve d’une terre paisible appelée Vinland."
    },
    {
        id: 20,
        titre: "Yo-kai Watch",
        auteur: "",
        tome: 1,
        prix: 9.10,
        categorie: "kodomo",
        image: "asset/img/yo-kai_watch_tome_1_page_de_couverture.jpg",
        description: "Yo-kai Watch raconte l’histoire de Nate Adams, un garçon ordinaire qui obtient une montre spéciale lui permettant de voir et d’invoquer des Yo-kai, des esprits invisibles responsables des petits problèmes du quotidien. Avec l’aide du majordome Yo-kai Whisper et du chat Jibanyan, il résout des situations étranges causées par ces créatures. Entre humour et aventures, la série montre que derrière chaque souci se cache souvent une leçon d’amitié et de compréhension."
    },
];