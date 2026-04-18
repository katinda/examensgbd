-- ============================================================================
-- PADEL MANAGER - Schema MySQL
-- 10 tables avec description (COMMENT) pour chacune
-- ============================================================================

CREATE DATABASE IF NOT EXISTS PadelManager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE PadelManager;


-- ----------------------------------------------------------------------------
-- Table : Sites
-- ----------------------------------------------------------------------------
CREATE TABLE Sites (
    Site_ID       INT AUTO_INCREMENT PRIMARY KEY,
    Nom           VARCHAR(100) NOT NULL UNIQUE,
    Adresse       VARCHAR(255),
    Ville         VARCHAR(100),
    Code_Postal   VARCHAR(10),
    Est_Actif     TINYINT(1) NOT NULL DEFAULT 1,
    Date_Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)
COMMENT = 'Sites physiques de padel exploites par la plateforme. Un site regroupe plusieurs terrains, a ses propres horaires d''ouverture par annee et peut avoir des jours de fermeture specifiques. Racine de la hierarchie des donnees d''exploitation.';


-- ----------------------------------------------------------------------------
-- Table : Terrains
-- ----------------------------------------------------------------------------
CREATE TABLE Terrains (
    Terrain_ID  INT AUTO_INCREMENT PRIMARY KEY,
    Site_ID     INT NOT NULL,
    Num_Terrain INT NOT NULL,
    Libelle     VARCHAR(50),
    Est_Actif   TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT FK_Terrains_Sites FOREIGN KEY (Site_ID) REFERENCES Sites(Site_ID),
    CONSTRAINT UX_Terrains_SiteNumero UNIQUE (Site_ID, Num_Terrain),
    CONSTRAINT CK_Terrains_Num CHECK (Num_Terrain > 0)
)
COMMENT = 'Terrains physiques rattaches a un site. La paire (Site_ID, Num_Terrain) est unique : deux sites peuvent chacun avoir un "Terrain 1" mais au sein d''un meme site les numeros sont uniques. Granularite minimale d''une reservation.';


-- ----------------------------------------------------------------------------
-- Table : Horaires_Sites
-- ----------------------------------------------------------------------------
CREATE TABLE Horaires_Sites (
    Horaire_ID  INT AUTO_INCREMENT PRIMARY KEY,
    Site_ID     INT NOT NULL,
    Annee       INT NOT NULL,
    Heure_Debut TIME NOT NULL,
    Heure_Fin   TIME NOT NULL,
    CONSTRAINT FK_Horaires_Sites_Sites FOREIGN KEY (Site_ID) REFERENCES Sites(Site_ID),
    CONSTRAINT UK_Horaires_SiteAnnee UNIQUE (Site_ID, Annee),
    CONSTRAINT CK_Horaires_Heures CHECK (Heure_Debut < Heure_Fin),
    CONSTRAINT CK_Horaires_Annee  CHECK (Annee BETWEEN 2000 AND 2100)
)
COMMENT = 'Horaires d''ouverture d''un site pour une annee civile. Heure_Debut = debut de la premiere reservation possible, Heure_Fin = fin de la derniere reservation possible. Les creneaux de 1h30 + 15 min de pause sont calcules dynamiquement par l''application a partir de ces horaires (non stockes en base). Un seul enregistrement par (Site, Annee).';


-- ----------------------------------------------------------------------------
-- Table : Fermetures
-- ----------------------------------------------------------------------------
CREATE TABLE Fermetures (
    Fermeture_ID  INT AUTO_INCREMENT PRIMARY KEY,
    Site_ID       INT NULL,
    Date_Debut    DATE NOT NULL,
    Date_Fin      DATE NOT NULL,
    Raison        VARCHAR(255),
    Date_Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Fermetures_Sites FOREIGN KEY (Site_ID) REFERENCES Sites(Site_ID),
    CONSTRAINT CK_Fermetures_Dates CHECK (Date_Debut <= Date_Fin)
)
COMMENT = 'Jours de fermeture bloquant toute reservation. Deux modes : global (Site_ID NULL, affecte tous les sites) ou local (Site_ID renseigne, affecte ce seul site). Peut couvrir une journee unique ou une plage. Consultee avant chaque tentative de reservation.';


-- ----------------------------------------------------------------------------
-- Table : Membres
-- ----------------------------------------------------------------------------
CREATE TABLE Membres (
    Membre_ID     INT AUTO_INCREMENT PRIMARY KEY,
    Matricule     VARCHAR(10) NOT NULL UNIQUE,
    Nom           VARCHAR(100) NOT NULL,
    Prenom        VARCHAR(100) NOT NULL,
    Email         VARCHAR(255),
    Telephone     VARCHAR(20),
    Categorie     CHAR(1) NOT NULL,
    Site_ID       INT NULL,
    Est_Actif     TINYINT(1) NOT NULL DEFAULT 1,
    Date_Creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Membres_Sites FOREIGN KEY (Site_ID) REFERENCES Sites(Site_ID),
    CONSTRAINT CK_Membres_Categorie CHECK (Categorie IN ('G','S','L')),
    CONSTRAINT CK_Membres_MatriculePrefix CHECK (
        (Categorie = 'G' AND Matricule LIKE 'G%') OR
        (Categorie = 'S' AND Matricule LIKE 'S%') OR
        (Categorie = 'L' AND Matricule LIKE 'L%')
    ),
    CONSTRAINT CK_Membres_SiteCoherence CHECK (
        (Categorie = 'S' AND Site_ID IS NOT NULL) OR
        (Categorie IN ('G','L') AND Site_ID IS NULL)
    )
)
COMMENT = 'Utilisateurs finaux authentifies par matricule uniquement (pas de mot de passe cote joueur). Trois categories mutuellement exclusives : G (Global, tous sites, 3 semaines d''anticipation), S (Site, site de rattachement uniquement, 2 semaines), L (Libre, tous sites, 5 jours). La lettre du matricule doit correspondre a la categorie.';


-- ----------------------------------------------------------------------------
-- Table : Administrateurs
-- ----------------------------------------------------------------------------
CREATE TABLE Administrateurs (
    Admin_ID          INT AUTO_INCREMENT PRIMARY KEY,
    Login             VARCHAR(50) NOT NULL UNIQUE,
    Mot_De_Passe_Hash VARCHAR(255) NOT NULL,
    Nom               VARCHAR(100),
    Prenom            VARCHAR(100),
    Email             VARCHAR(255),
    Type              VARCHAR(10) NOT NULL,
    Site_ID           INT NULL,
    Est_Actif         TINYINT(1) NOT NULL DEFAULT 1,
    Date_Creation     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Admins_Sites FOREIGN KEY (Site_ID) REFERENCES Sites(Site_ID),
    CONSTRAINT CK_Admins_Type CHECK (Type IN ('GLOBAL','SITE')),
    CONSTRAINT CK_Admins_SiteCoherence CHECK (
        (Type = 'SITE'   AND Site_ID IS NOT NULL) OR
        (Type = 'GLOBAL' AND Site_ID IS NULL)
    )
)
COMMENT = 'Comptes administrateurs pour l''interface d''administration. Authentifies par login + hash de mot de passe (bcrypt/argon2). Type GLOBAL = acces a tous les sites, seul autorise a lever une penalite manuellement. Type SITE = acces limite au Site_ID renseigne.';


-- ----------------------------------------------------------------------------
-- Table : Reservations
-- ----------------------------------------------------------------------------
CREATE TABLE Reservations (
    Reservation_ID  INT AUTO_INCREMENT PRIMARY KEY,
    Terrain_ID      INT NOT NULL,
    Organisateur_ID INT NOT NULL,
    Date_Match      DATE NOT NULL,
    Heure_Debut     TIME NOT NULL,
    Heure_Fin       TIME NOT NULL,
    Type            VARCHAR(10) NOT NULL,
    Etat            VARCHAR(20) NOT NULL DEFAULT 'EN_COURS',
    Prix_Total      DECIMAL(6,2) NOT NULL DEFAULT 60.00,
    Date_Creation   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastUpdate      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT FK_Reservations_Terrains FOREIGN KEY (Terrain_ID) REFERENCES Terrains(Terrain_ID),
    CONSTRAINT FK_Reservations_Organisateur FOREIGN KEY (Organisateur_ID) REFERENCES Membres(Membre_ID),
    CONSTRAINT UX_Reservations_TerrainDateHeure UNIQUE (Terrain_ID, Date_Match, Heure_Debut),
    CONSTRAINT CK_Reservations_Type CHECK (Type IN ('PRIVE','PUBLIC')),
    CONSTRAINT CK_Reservations_Etat CHECK (Etat IN ('EN_COURS','COMPLETEE','BASCULE_PUBLIC','ANNULEE','FORFAIT','TERMINEE')),
    CONSTRAINT CK_Reservations_Heures CHECK (Heure_Debut < Heure_Fin),
    CONSTRAINT CK_Reservations_Prix CHECK (Prix_Total > 0)
)
COMMENT = 'Coeur metier : reservations d''un creneau de 1h30 sur un terrain a une date donnee. Le couple (Terrain_ID, Date_Match, Heure_Debut) est unique. Type PRIVE (organisateur invite les 3 autres) ou PUBLIC (3 places ouvertes, premier paye premier servi). Etat suit un cycle de vie : EN_COURS -> COMPLETEE/BASCULE_PUBLIC -> TERMINEE (ou ANNULEE/FORFAIT). Prix : 60 EUR divise en 4 parts de 15 EUR.';


-- ----------------------------------------------------------------------------
-- Table : Inscriptions
-- ----------------------------------------------------------------------------
CREATE TABLE Inscriptions (
    Inscription_ID   INT AUTO_INCREMENT PRIMARY KEY,
    Reservation_ID   INT NOT NULL,
    Membre_ID        INT NOT NULL,
    Date_Inscription DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Est_Organisateur TINYINT(1) NOT NULL DEFAULT 0,
    LastUpdate       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT FK_Inscriptions_Reservations FOREIGN KEY (Reservation_ID) REFERENCES Reservations(Reservation_ID) ON DELETE CASCADE,
    CONSTRAINT FK_Inscriptions_Membres FOREIGN KEY (Membre_ID) REFERENCES Membres(Membre_ID),
    CONSTRAINT UX_Inscriptions_ResMembre UNIQUE (Reservation_ID, Membre_ID)
)
COMMENT = 'Relation n-n entre Reservations et Membres : liste des 4 joueurs maximum d''un match. Un match complet = 4 inscriptions. Un joueur ne peut etre inscrit qu''une fois par match. L''organisateur a sa propre inscription avec Est_Organisateur=1, creee automatiquement avec la reservation. La limite stricte de 4 joueurs est controlee par la couche applicative.';


-- ----------------------------------------------------------------------------
-- Table : Paiements
-- ----------------------------------------------------------------------------
CREATE TABLE Paiements (
    Paiement_ID       INT AUTO_INCREMENT PRIMARY KEY,
    Inscription_ID    INT NOT NULL UNIQUE,
    Montant           DECIMAL(6,2) NOT NULL,
    Date_Paiement     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    Methode           VARCHAR(20),
    Est_Annule        TINYINT(1) NOT NULL DEFAULT 0,
    Montant_Rembourse DECIMAL(6,2) NULL,
    Date_Annulation   DATETIME NULL,
    CONSTRAINT FK_Paiements_Inscriptions FOREIGN KEY (Inscription_ID) REFERENCES Inscriptions(Inscription_ID),
    CONSTRAINT CK_Paiements_Montant CHECK (Montant > 0),
    CONSTRAINT CK_Paiements_Methode CHECK (Methode IS NULL OR Methode IN ('CARTE','VIREMENT','ESPECES','MOBILE')),
    CONSTRAINT CK_Paiements_Remboursement CHECK (
        (Est_Annule = 0 AND Montant_Rembourse IS NULL AND Date_Annulation IS NULL) OR
        (Est_Annule = 1 AND Date_Annulation IS NOT NULL)
    )
)
COMMENT = 'Paiements effectues par les joueurs, un par inscription (relation 1-1 via UNIQUE sur Inscription_ID). Un paiement non encore effectue = absence de ligne. Les annulations ne suppriment pas la ligne : on renseigne Est_Annule, Montant_Rembourse et Date_Annulation pour preserver la tracabilite du chiffre d''affaires historique.';


-- ----------------------------------------------------------------------------
-- Table : Penalites
-- ----------------------------------------------------------------------------
CREATE TABLE Penalites (
    Penalite_ID    INT AUTO_INCREMENT PRIMARY KEY,
    Membre_ID      INT NOT NULL,
    Reservation_ID INT NULL,
    Date_Debut     DATE NOT NULL,
    Date_Fin       DATE NOT NULL,
    Cause          VARCHAR(30) NOT NULL,
    Levee          TINYINT(1) NOT NULL DEFAULT 0,
    Levee_Par      INT NULL,
    Levee_Le       DATETIME NULL,
    Levee_Raison   VARCHAR(500) NULL,
    Date_Creation  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Penalites_Membres FOREIGN KEY (Membre_ID) REFERENCES Membres(Membre_ID),
    CONSTRAINT FK_Penalites_Reservations FOREIGN KEY (Reservation_ID) REFERENCES Reservations(Reservation_ID),
    CONSTRAINT FK_Penalites_LeveePar FOREIGN KEY (Levee_Par) REFERENCES Administrateurs(Admin_ID),
    CONSTRAINT CK_Penalites_Cause CHECK (Cause IN ('PRIVATE_INCOMPLETE','PAYMENT_MISSING','OTHER')),
    CONSTRAINT CK_Penalites_Dates CHECK (Date_Debut <= Date_Fin),
    CONSTRAINT CK_Penalites_Levee CHECK (
        (Levee = 0 AND Levee_Par IS NULL AND Levee_Le IS NULL AND Levee_Raison IS NULL) OR
        (Levee = 1 AND Levee_Par IS NOT NULL AND Levee_Le IS NOT NULL AND Levee_Raison IS NOT NULL)
    )
)
COMMENT = 'Penalites de 7 jours de delai supplementaire imposees aux membres. Deux causes automatiques : PRIVATE_INCOMPLETE (match prive incomplet bascule en public - penalise l''organisateur) et PAYMENT_MISSING (joueur non paye la veille du match). Une cause manuelle : OTHER (saisie par un admin). Actif tant que Levee=0 et date courante dans [Date_Debut, Date_Fin]. Seuls les admins GLOBAL peuvent lever une penalite, avec justification obligatoire.';
