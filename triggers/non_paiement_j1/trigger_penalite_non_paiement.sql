-- ============================================================
-- TRIGGER : Applique la pénalité après suppression d'une inscription
-- Se déclenche automatiquement après chaque DELETE sur Inscriptions.
-- Vérifie que c'est bien un cas de non-paiement à J-1 avant de pénaliser.
-- ============================================================

DELIMITER $$

CREATE TRIGGER trg_penalite_apres_suppression_inscription  -- Nom du trigger
AFTER DELETE ON Inscriptions                               -- Se déclenche après chaque DELETE sur Inscriptions
FOR EACH ROW                                               -- Traite chaque ligne supprimée individuellement
BEGIN

    -- Vérifie que la suppression concerne bien un match de demain sans paiement
    -- (pour ne pas pénaliser des suppressions manuelles hors contexte J-1)
    IF EXISTS (
        SELECT 1
        FROM   Reservations r
        WHERE  r.Reservation_ID = OLD.Reservation_ID          -- Le match de l'inscription supprimée
          AND  r.Date_Match = CURDATE() + INTERVAL 1 DAY      -- Le match est bien demain (J-1)
          AND  r.Etat IN ('EN_COURS', 'BASCULE_PUBLIC')       -- Le match est encore actif
    )
    AND NOT EXISTS (
        SELECT 1
        FROM   Paiements p
        WHERE  p.Inscription_ID = OLD.Inscription_ID          -- Vérifie l'absence de paiement valide
          AND  p.Est_Annule = 0
    )
    THEN
        -- Insère la pénalité pour le membre dont l'inscription vient d'être supprimée
        INSERT INTO Penalites (Membre_ID, Reservation_ID, Date_Debut, Date_Fin, Cause)
        VALUES (
            OLD.Membre_ID,                 -- Le membre qui n'a pas payé
            OLD.Reservation_ID,           -- Le match concerné
            CURDATE(),                    -- Pénalité commence aujourd'hui
            CURDATE() + INTERVAL 7 DAY,  -- Pénalité dure 7 jours
            'PAYMENT_MISSING'             -- Cause : paiement absent à J-1
        );
    END IF;

END$$

DELIMITER ;  -- Remet le délimiteur par défaut
