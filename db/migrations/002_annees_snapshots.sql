-- 2019 : clôture des années et états signés
CREATE TABLE annees (
    code         TEXT PRIMARY KEY,
    cloturee     INTEGER NOT NULL DEFAULT 0,
    date_cloture TEXT,
    cloture_par  TEXT,
    commentaire  TEXT
);

CREATE TABLE snapshots (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    annee          TEXT NOT NULL,
    teacher_id     TEXT NOT NULL,
    nom            TEXT,
    prenom         TEXT,
    statut         TEXT,
    service_du     REAL,
    decharges      REAL DEFAULT 0,
    total_etd      REAL,
    ajustements    REAL DEFAULT 0,
    total_officiel REAL,
    solde          REAL,
    observations   TEXT,
    created_at     TEXT DEFAULT CURRENT_TIMESTAMP
);
