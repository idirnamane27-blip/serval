-- SERVAL - schéma de la base (v1.0, 2016)
-- Les évolutions ultérieures sont dans db/migrations/

CREATE TABLE teachers (
    id          TEXT PRIMARY KEY,
    nom         TEXT NOT NULL,
    prenom      TEXT NOT NULL,
    statut      TEXT NOT NULL,              -- EC, PRAG, ATER, VAC
    service_du  REAL NOT NULL DEFAULT 0,    -- service statutaire en heures TD
    email       TEXT,
    actif       INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE services (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    annee       TEXT NOT NULL,              -- AAAA-AAAA
    teacher_id  TEXT,
    ue          TEXT,
    libelle     TEXT,
    type        TEXT NOT NULL,              -- CM, TD, TP, PROJET
    heures      REAL,
    groupes     INTEGER DEFAULT 1,
    part        REAL,
    commentaire TEXT,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP
);

-- heures complémentaires transmises à la paie
CREATE TABLE heures_comp (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    teacher_id    TEXT NOT NULL,
    annee         TEXT NOT NULL,
    heures        REAL NOT NULL,
    taux          REAL NOT NULL,
    date_paiement TEXT
);
