INSERT INTO rakatanga_clean.oldreservations (
  langue,
  nbpilotes,
  nb_accomp,
  commentaire,
  log,
  codepromo,
  montant,
  reduction,
  totalttc,
  notes,
  statut,
  origine_ajout,
  date_ajout,
  date_paiement_1,
  date_paiement_2
  )
SELECT langue,
  nbpilotes,
  nbpassagers,
  commentaire,
  log,
  codepromo,
  montant,
  reduction,
  totalttc,
  notes,
  statut,
  origine_ajout,
  date_ajout,
  date_paiement_1,
  date_paiement_2
FROM rakatanghntour.reservations
WHERE rakatanghntour.reservations.date_ajout 
NOT IN (SELECT date_ajout FROM rakatanga_clean.oldreservations);
