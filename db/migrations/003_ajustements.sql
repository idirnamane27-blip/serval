-- 2019 : corrections manuelles validées par la direction
CREATE TABLE adjustments (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    annee           TEXT NOT NULL,
    teacher_id      TEXT NOT NULL,
    heures_etd      REAL NOT NULL,
    motif           TEXT,
    valide_par      TEXT,
    date_validation TEXT,
    origine         TEXT,
    created_at      TEXT DEFAULT CURRENT_TIMESTAMP
);
