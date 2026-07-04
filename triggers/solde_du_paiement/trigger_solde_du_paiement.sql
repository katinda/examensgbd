-- ============================================================
-- TRIGGER : Agrège le solde dû au paiement d'un joueur
-- Se déclenche avant chaque INSERT sur Paiements.
-- Si le membre a un solde impayé (match public en FORFAIT dont
-- il était organisateur), ce solde est ajouté au montant payé.
--
-- La formule de déficit ci-dessous doit rester identique à celle de
-- ReservationRepository::hasSoldeDu() (utilisée pour bloquer une nouvelle
-- réservation tant que ce solde n'est pas réglé). Etat = FORFAIT n'est
-- jamais positionné que par evt_cloture_matches (triggers/cloture_matches/).
-- ============================================================

DELIMITER $$

CREATE TRIGGER trg_agregation_solde_du
BEFORE INSERT ON Paiements                  -- Avant l'insertion : on peut encore modifier NEW.Montant
FOR EACH ROW
BEGIN

    DECLARE v_membre_id  INT;
    DECLARE v_solde_du   DECIMAL(6,2) DEFAULT 0;

    -- Récupère le membre qui paie via son inscription
    SELECT i.Membre_ID
    INTO   v_membre_id
    FROM   Inscriptions i
    WHERE  i.Inscription_ID = NEW.Inscription_ID;

    -- Calcule le solde total dû : somme des déficits sur les matchs en FORFAIT dont il est organisateur
    -- Déficit = Prix_Total du match - somme des paiements valides reçus pour ce match
    SELECT COALESCE(
        SUM(
            r.Prix_Total - COALESCE((
                SELECT SUM(p.Montant)
                FROM   Paiements p
                INNER JOIN Inscriptions i2 ON i2.Inscription_ID = p.Inscription_ID
                WHERE  i2.Reservation_ID = r.Reservation_ID
                  AND  p.Est_Annule = 0      -- On ignore les paiements annulés
            ), 0)
        ), 0)
    INTO  v_solde_du
    FROM  Reservations r
    WHERE r.Organisateur_ID = v_membre_id
      AND r.Etat = 'FORFAIT';               -- Uniquement les matchs dont le solde n'a pas encore été réglé

    -- Si un solde existe, on l'ajoute au montant du paiement en cours
    IF v_solde_du > 0 THEN

        SET NEW.Montant = NEW.Montant + v_solde_du;  -- Le membre paie sa part + son solde en une seule transaction

        -- Les matchs FORFAIT deviennent TERMINEE : le solde est maintenant couvert
        UPDATE Reservations
        SET    Etat = 'TERMINEE'
        WHERE  Organisateur_ID = v_membre_id
          AND  Etat = 'FORFAIT';

    END IF;

END$$

DELIMITER ;
