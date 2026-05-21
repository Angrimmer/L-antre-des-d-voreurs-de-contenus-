-- Migration 02 — saison en cours de diffusion
-- À exécuter dans phpMyAdmin sur la base antre_devolib

USE antre_devolib;

ALTER TABLE library_items
    ADD COLUMN airing_season SMALLINT DEFAULT NULL AFTER current_season;
