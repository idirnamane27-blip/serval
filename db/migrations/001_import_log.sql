-- 2018 : import des classeurs Excel
CREATE TABLE import_log (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    fichier     TEXT NOT NULL,
    annee       TEXT,
    statut      TEXT NOT NULL,              -- STARTED, OK, ERROR
    nb_lignes   INTEGER,
    message     TEXT,
    started_at  TEXT DEFAULT CURRENT_TIMESTAMP,
    finished_at TEXT
);
ALTER TABLE services ADD COLUMN source TEXT;
ALTER TABLE services ADD COLUMN import_id INTEGER;
