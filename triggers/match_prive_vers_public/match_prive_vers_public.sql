-- ============================================================
-- Règle métier : Match privé → public la veille (J-1)
-- Si les 4 joueurs ne sont pas atteints la veille du match,
-- le match bascule automatiquement en PUBLIC et une pénalité
-- de 7 jours est appliquée à l'organisateur.
--
-- Note : cette règle est basée sur le temps (J-1), elle ne peut
-- pas être un TRIGGER classique (qui réagit à un INSERT/UPDATE/DELETE).
-- On utilise un EVENT MySQL qui tourne chaque soir à 23h.
-- ============================================================

DELIMITER $$  -- Change le délimiteur pour pouvoir écrire des blocs BEGIN...END sans conflit avec le ";"

CREATE EVENT IF NOT EXISTS evt_bascule_match_prive_public  -- Crée l'event seulement s'il n'existe pas déjà
ON SCHEDULE EVERY 1 DAY                                    -- L'event se répète toutes les 24h
STARTS (CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 23 HOUR) -- Démarre demain soir à 23h00
COMMENT 'Bascule les matchs privés incomplets en public la veille du match (J-1)'
DO
BEGIN  -- Début du bloc de code exécuté à chaque déclenchement

    DECLARE v_reservation_id  INT;  -- Variable pour stocker l'ID du match en cours de traitement
    DECLARE v_organisateur_id INT;  -- Variable pour stocker l'ID de l'organisateur du match
    DECLARE done              INT DEFAULT FALSE;  -- Indicateur de fin de curseur (FALSE = il reste des lignes)

    -- Curseur : sélectionne tous les matchs privés de demain avec moins de 4 joueurs inscrits
    DECLARE cur CURSOR FOR
        SELECT r.Reservation_ID, r.Organisateur_ID  -- On récupère uniquement ce dont on a besoin
        FROM   Reservations r
        WHERE  r.Type  = 'PRIVE'                          -- Uniquement les matchs privés
          AND  r.Etat  = 'EN_COURS'                       -- Uniquement les matchs pas encore traités
          AND  r.Date_Match = CURDATE() + INTERVAL 1 DAY  -- Uniquement les matchs de demain (J-1)
          AND (
              SELECT COUNT(*)          -- Compte le nombre de joueurs inscrits pour ce match
              FROM   Inscriptions i
              WHERE  i.Reservation_ID = r.Reservation_ID
          ) < 4;                       -- Condition : moins de 4 joueurs → match incomplet

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;  -- Quand le curseur est vide, on passe done à TRUE pour sortir de la boucle

    OPEN cur;  -- Ouvre le curseur et exécute la requête SELECT ci-dessus

    bascule_loop: LOOP  -- Début de la boucle qui traite chaque match un par un

        FETCH cur INTO v_reservation_id, v_organisateur_id;  -- Récupère la ligne suivante du curseur dans les variables
        IF done THEN LEAVE bascule_loop; END IF;             -- Si plus de lignes, on sort de la boucle

        -- 1. Basculer le match : Type PUBLIC, Etat BASCULE_PUBLIC
        UPDATE Reservations
        SET    Type = 'PUBLIC',          -- Le match devient visible et rejoignable par tous
               Etat = 'BASCULE_PUBLIC'  -- On trace que ce match était privé et a été forcé en public
        WHERE  Reservation_ID = v_reservation_id;  -- Uniquement pour le match en cours de traitement

        -- 2. Appliquer une pénalité de 7 jours à l'organisateur
        INSERT INTO Penalites (Membre_ID, Reservation_ID, Date_Debut, Date_Fin, Cause)
        VALUES (
            v_organisateur_id,              -- L'organisateur est responsable du match incomplet
            v_reservation_id,              -- On lie la pénalité au match concerné
            CURDATE(),                     -- La pénalité commence aujourd'hui
            CURDATE() + INTERVAL 7 DAY,   -- La pénalité dure 7 jours
            'PRIVATE_INCOMPLETE'           -- Cause : match privé non complété avant J-1
        );

    END LOOP;  -- Fin de la boucle, on passe au match suivant

    CLOSE cur;  -- Ferme le curseur et libère les ressources

END$$  -- Fin du bloc de code de l'event

DELIMITER ;  -- Remet le délimiteur par défaut ";"
