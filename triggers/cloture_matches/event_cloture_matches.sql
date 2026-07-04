-- ============================================================
-- EVENT : Clôture les matchs dont l'heure de début est passée
-- Tourne à 23h45, après evt_liberer_places_non_paiement (22h) et
-- evt_bascule_match_prive_public (23h).
-- Sans cet event, Etat reste EN_COURS/BASCULE_PUBLIC pour toujours :
-- FORFAIT et TERMINEE ne sont positionnés nulle part ailleurs, et
-- le trigger trg_agregation_solde_du (et ReservationRepository::hasSoldeDu())
-- ne peuvent jamais trouver de solde dû sans cette clôture.
-- ============================================================

DELIMITER $$

CREATE EVENT IF NOT EXISTS evt_cloture_matches
ON SCHEDULE EVERY 1 DAY
STARTS (CURRENT_DATE + INTERVAL 1 DAY + INTERVAL 23 HOUR + INTERVAL 45 MINUTE)
COMMENT 'Cloture les matchs commences : TERMINEE si 4 joueurs payes, FORFAIT sinon'
DO
BEGIN

    DECLARE v_reservation_id INT;
    DECLARE v_nb_payes       INT;
    DECLARE done             INT DEFAULT FALSE;

    -- Curseur : tous les matchs encore actifs dont l'heure de début est déjà passée.
    -- Comparaison sur l'instant exact (pas juste la date) pour ne pas dépendre du fait
    -- que l'event tourne bien chaque nuit à heure fixe.
    DECLARE cur CURSOR FOR
        SELECT r.Reservation_ID
        FROM   Reservations r
        WHERE  r.Etat IN ('EN_COURS', 'BASCULE_PUBLIC')
          AND  TIMESTAMP(r.Date_Match, r.Heure_Debut) <= NOW();

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    cloture_loop: LOOP

        FETCH cur INTO v_reservation_id;
        IF done THEN LEAVE cloture_loop; END IF;

        -- Nombre de joueurs ayant réellement payé (paiements non annulés)
        SELECT COUNT(*)
        INTO   v_nb_payes
        FROM   Inscriptions i
        JOIN   Paiements p ON p.Inscription_ID = i.Inscription_ID
        WHERE  i.Reservation_ID = v_reservation_id
          AND  p.Est_Annule = 0;

        IF v_nb_payes >= 4 THEN
            -- Match complet et intégralement payé : terminé normalement
            UPDATE Reservations SET Etat = 'TERMINEE' WHERE Reservation_ID = v_reservation_id;
        ELSE
            -- Moins de 4 payés : l'organisateur doit le solde (recouvré au prochain
            -- paiement via trg_agregation_solde_du, et bloque createReservation via
            -- ReservationRepository::hasSoldeDu() tant que non réglé)
            UPDATE Reservations SET Etat = 'FORFAIT' WHERE Reservation_ID = v_reservation_id;
        END IF;

    END LOOP;

    CLOSE cur;

END$$

DELIMITER ;
