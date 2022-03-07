-- 2022 : décharges de service
CREATE TABLE decharges (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    annee      TEXT NOT NULL,
    teacher_id TEXT NOT NULL,
    heures     REAL NOT NULL,
    motif      TEXT
);
