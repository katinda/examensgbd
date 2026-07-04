-- ============================================================
-- EVENT : Libère les places non payées à J-1
-- Tourne à 22h, avant le basculement privé→public (23h).
-- Le DELETE sur Inscriptions déclenche automatiquement le trigger.
-- ============================================================

DELIMITER $$

CREATE EVENT IF NOT EXISTS evt_liberer_places_non_paiement  -- Crée l'event s'il n'existe pas déjà
ON SCHEDULE EVERY 1 DAY                                     -- Tourne toutes les 24h
STARTS (CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 22 HOUR)  -- Démarre demain à 22h (avant le basculement privé→public à 23h)
COMMENT 'Supprime les inscriptions non payées à J-1 pour libérer les places'
DO
BEGIN  -- Début du bloc exécuté à chaque déclenchement

    DECLARE v_inscription_id INT;   -- Variable pour l'ID de l'inscription non payée
    DECLARE done             INT DEFAULT FALSE;  -- Indicateur de fin de curseur

    -- Curseur : toutes les inscriptions de demain sans paiement valide
    DECLARE cur CURSOR FOR
        SELECT i.Inscription_ID                                            -- On récupère uniquement l'ID de l'inscription
        FROM   Inscriptions i
        INNER JOIN Reservations r ON r.Reservation_ID = i.Reservation_ID  -- Pour filtrer sur la date du match
        LEFT  JOIN Paiements p   ON p.Inscription_ID  = i.Inscription_ID  -- Pour détecter l'absence de paiement
                                AND p.Est_Annule = 0                       -- On ignore les paiements annulés
        WHERE  r.Date_Match = CURDATE() + INTERVAL 1 DAY                  -- Matchs de demain uniquement (J-1)
          AND  r.Etat IN ('EN_COURS', 'BASCULE_PUBLIC')                   -- Matchs encore actifs
          AND  p.Paiement_ID IS NULL;                                      -- Aucun paiement trouvé = joueur non payé

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;  -- Quand plus de lignes, passer done à TRUE

    OPEN cur;  -- Exécute le SELECT du curseur

    liberation_loop: LOOP  -- Boucle sur chaque inscription non payée

        FETCH cur INTO v_inscription_id;               -- Récupère l'ID de l'inscription suivante
        IF done THEN LEAVE liberation_loop; END IF;    -- Sort de la boucle si terminé

        -- Supprime l'inscription → libère la place ET déclenche le trigger trg_penalite_apres_suppression_inscription
        DELETE FROM Inscriptions
        WHERE Inscription_ID = v_inscription_id;

    END LOOP;  -- Passe à l'inscription suivante

    CLOSE cur;  -- Ferme le curseur et libère les ressources

END$$

DELIMITER ;  -- Remet le délimiteur par défaut
