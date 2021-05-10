-- 2021 : refonte (cache des totaux, valeurs HETD imposées, nom d'origine)
CREATE TABLE cache_totaux (
    annee       TEXT NOT NULL,
    teacher_id  TEXT NOT NULL,
    total_etd   REAL NOT NULL,
    nb_lignes   INTEGER,
    computed_at TEXT,
    PRIMARY KEY (annee, teacher_id)
);
ALTER TABLE services ADD COLUMN teacher_nom_source TEXT;
ALTER TABLE services ADD COLUMN etd_impose REAL;
