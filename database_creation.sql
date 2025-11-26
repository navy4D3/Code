CREATE DATABASE IF NOT EXISTS ecoride5;
USE ecoride5;

CREATE TABLE user (
    id INT AUTO_INCREMENT NOT NULL,
    email VARCHAR(180) NOT NULL,
    roles JSON NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(15) NOT NULL,
    photo_profil LONGBLOB DEFAULT NULL,
    adresse VARCHAR(255) NOT NULL,
    date_naissance DATETIME NOT NULL,
    is_verified TINYINT(1) NOT NULL,
    note DOUBLE PRECISION DEFAULT NULL,
    description LONGTEXT DEFAULT NULL,
    preferences LONGTEXT DEFAULT NULL,
    credits INT NOT NULL,
    UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
    PRIMARY KEY(id)
);

CREATE TABLE voiture (
    id INT AUTO_INCREMENT NOT NULL,
    proprietaire_id INT NOT NULL,
    modele VARCHAR(50) NOT NULL,
    marque VARCHAR(255) NOT NULL,
    is_electric TINYINT(1) NOT NULL,
    immatriculation VARCHAR(20) NOT NULL,
    date_premiere_immatriculation DATETIME NOT NULL,
    couleur VARCHAR(255) NOT NULL,
    places INT NOT NULL,
    surnom VARCHAR(50) NOT NULL,
    INDEX IDX_E9E2810F76C50E4A (proprietaire_id),
    PRIMARY KEY(id)
);

CREATE TABLE trajet (
    id INT AUTO_INCREMENT NOT NULL,
    voiture_id INT NOT NULL,
    chauffeur_id INT NOT NULL,
    heure_depart DATETIME NOT NULL,
    lieu_depart VARCHAR(100) NOT NULL,
    lieu_arrivee VARCHAR(100) NOT NULL,
    statut VARCHAR(255) NOT NULL,
    prix_personne INT NOT NULL,
    duree_in_seconds INT NOT NULL,
    INDEX IDX_2B5BA98C181A8BA (voiture_id),
    INDEX IDX_2B5BA98C85C0B3BE (chauffeur_id),
    PRIMARY KEY(id)
); 

CREATE TABLE avis (
    id INT AUTO_INCREMENT NOT NULL,
    creator_id INT NOT NULL,
    user_id INT NOT NULL,
    trajet_id INT NOT NULL,
    commentaire LONGTEXT DEFAULT NULL,
    is_positive TINYINT(1) NOT NULL,
    note INT NOT NULL,
    statut VARCHAR(255) NOT NULL,
    INDEX IDX_8F91ABF061220EA6 (creator_id),
    INDEX IDX_8F91ABF0A76ED395 (user_id),
    INDEX IDX_8F91ABF0D12A823 (trajet_id),
    PRIMARY KEY(id)
); 

CREATE TABLE reservation (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    trajet_id INT NOT NULL,
    nb_places INT NOT NULL,
    statut VARCHAR(255) NOT NULL,
    INDEX IDX_42C84955A76ED395 (user_id),
    INDEX IDX_42C84955D12A823 (trajet_id),
    PRIMARY KEY(id)
); 

CREATE TABLE trajet_user (
    trajet_id INT NOT NULL,
    user_id INT NOT NULL,
    INDEX IDX_825A9176D12A823 (trajet_id),
    INDEX IDX_825A9176A76ED395 (user_id),
    PRIMARY KEY(trajet_id, user_id)
); 

-- Contraintes de clés étrangères
ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF061220EA6 FOREIGN KEY (creator_id) REFERENCES user (id);
ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0D12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id);

ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE reservation ADD CONSTRAINT FK_42C84955D12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id);

ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C181A8BA FOREIGN KEY (voiture_id) REFERENCES voiture (id);
ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98C85C0B3BE FOREIGN KEY (chauffeur_id) REFERENCES user (id);

ALTER TABLE trajet_user ADD CONSTRAINT FK_825A9176D12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id) ON DELETE CASCADE;
ALTER TABLE trajet_user ADD CONSTRAINT FK_825A9176A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE;

ALTER TABLE voiture ADD CONSTRAINT FK_E9E2810F76C50E4A FOREIGN KEY (proprietaire_id) REFERENCES user (id);

-- Création des jeux de données
INSERT INTO user (email, roles, password, nom, prenom, telephone, adresse, date_naissance, is_verified, note, description, preferences, credits)
VALUES
('admin@ecoride.com', '["ROLE_ADMIN"]', 'password1', 'Dupont', 'Admin', '0600000001', '1 rue de Paris, Lyon', '1990-01-01', 1, 4.8, '', NULL, 0),
('employee@ecoride.com', '["ROLE_EMPLOYE"]', 'password2', 'Martin', 'Employe', '0600000002', '2 rue des Fleurs, Marseille', '1992-03-14', 1, NULL, '', NULL, 0),
('chauffeur1@ecoride.com', '["ROLE_CHAUFFEUR"]', 'password3', 'Bernard', 'Lucas', '0600000003', '12 avenue des Alpes, Grenoble', '1985-07-20', 1, 4.6, 'Bienvenue sur mon profil', 'ANIMAL_LOVER', 120),
('chauffeur2@ecoride.com', '["ROLE_CHAUFFEUR"]', 'password4', 'Moreau', 'Clara', '0600000004', '5 boulevard Paul Bert, Nice', '1989-09-12', 1, 4.9, "Salut",'QUIET', 180),
('chauffeur3@ecoride.com', '["ROLE_CHAUFFEUR"]', 'password5', 'Leroy', 'Samir', '0600000005', '8 rue du Rhône, Lyon', '1991-11-04', 1, 4.4, "Soyez calme",'NONFUMEUR', 160),
('user1@ecoride.com', '["ROLE_USER"]', 'password6', 'Robert', 'Yvan', '0600000006', '10 rue centrale, Lyon', '1998-05-10', 1, 4.7, "", 'FUMEUR', 80),
('user2@ecoride.com', '["ROLE_USER"]', 'password7', 'Lefevre', 'Julie', '0600000007', '33 rue Nationale, Paris', '2000-02-18', 1, NULL, NULL, NULL, 50),
('user3@ecoride.com', '["ROLE_USER"]', 'password8', 'Garcia', 'Paul', '0600000008', 'Place Bellecour, Lyon', '1995-10-11', 1, NULL, NULL, NULL, 60),
('user4@ecoride.com', '["ROLE_USER"]', 'password9', 'Rossi', 'Laura', '0600000009', 'Cours Lafayette, Lyon', '2001-08-21', 1, NULL, NULL, NULL, 70),
('user5@ecoride.com', '["ROLE_USER"]', 'password10', 'Nguyen', 'Bao', '0600000010', 'Rue de Marseille, Montpellier', '1993-04-02', 1, NULL, NULL, NULL, 90);

INSERT INTO voiture (proprietaire_id, modele, marque, is_electric, immatriculation, date_premiere_immatriculation, couleur, places, surnom)
VALUES
(3, 'Model 3', 'Tesla', 1, 'AB-123-CD', '2020-03-20', 'Blanc', 4, 'La Fusée'),
(4, 'Clio', 'Renault', 0, 'CD-456-EF', '2018-06-15', 'Bleu', 3, 'Petite Bleue'),
(5, '208', 'Peugeot', 0, 'EF-789-GH', '2019-09-10', 'Noir', 4, 'Panthère'),
(3, 'ID.4', 'Volkswagen', 1, 'GH-111-IJ', '2021-11-05', 'Gris', 4, 'Electrocar');

INSERT INTO trajet (voiture_id, chauffeur_id, heure_depart, lieu_depart, lieu_arrivee, statut, prix_personne, duree_in_seconds)
VALUES
(1, 3, '2026-01-12 08:00:00', 'Paris, France', 'Lyon, France', 'Planifie', 15, 5400),
(2, 4, '2026-01-13 09:00:00', 'Lyon, France', 'Cannes, France', 'EnCours', 8, 1800),
(3, 5, '2026-01-14 07:30:00', 'Paris, France', 'Rennes, France', 'Termine', 30, 16200),
(4, 3, '2026-01-15 10:00:00', 'Grenoble, France', 'Chambery, France', 'Termine', 10, 3600),
(1, 3, '2026-01-16 06:00:00', 'Paris, France', 'Marseille, France', 'Termine', 25, 13500),
(2, 4, '2026-01-17 14:00:00', 'Lyon, France', 'Monaco, France', 'Termine', 6, 1500);

INSERT INTO reservation (user_id, trajet_id, nb_places, statut)
VALUES
(2, 1, 1, 'Enregistre'),
(3, 1, 1, 'Enregistre'),
(4, 2, 1, 'Enregistre'),
(5, 3, 2, 'Rembourse'),
(6, 4, 1, 'Equilibre'),
(6, 5, 1, 'Paye');

INSERT INTO avis (creator_id, user_id, trajet_id, commentaire, is_positive, note, statut)
VALUES
(6, 5, 4, 'Très ponctuel, mais musique un peu forte.', 0, 3, 'NonVisible'),
(6, 3, 5, 'Excellent trajet comme toujours.', 1, 5, 'Visible');



