-- Migration 01 — champs de suivi par statut
-- À exécuter dans phpMyAdmin sur la base antre_devolib

USE antre_devolib;

ALTER TABLE library_items
    ADD COLUMN planned_date    DATE         DEFAULT NULL AFTER status,
    ADD COLUMN current_episode SMALLINT     DEFAULT NULL AFTER planned_date,
    ADD COLUMN current_season  SMALLINT     DEFAULT NULL AFTER current_episode,
    ADD COLUMN temp_review     TEXT         DEFAULT NULL AFTER current_season,
    ADD COLUMN final_review    TEXT         DEFAULT NULL AFTER temp_review;
