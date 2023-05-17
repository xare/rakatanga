INSERT INTO rakatanga_clean.inscriptions (
  langue,
  nom,
  prenom,
  telephone,
  email,
  position,
  arrhes,
  solde,
  assurance,
  vols,
  statut,
  remarque,
  date_ajout
) 
SELECT langue,
  nom,
  prenom,
  telephone,
  email,
  position,
  arrhes,
  solde,
  assurance,
  vols,
  statut,
  remarque,
  date_ajout 
FROM rakatanghntour.inscriptions
WHERE rakatanghntour.inscriptions.date_ajout
NOT IN (SELECT date_ajout FROM rakatanga_clean.inscriptions);