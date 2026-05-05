-- Migración: Agregar columna hash_id a tabla orders (si no existe)
-- Ejecutar en producción: citrob.store (62.146.181.70)
-- Fecha: 2026-05-05

-- Agregar hash_id si no existe
SET @exist := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'citrobbd' 
               AND TABLE_NAME = 'orders' 
               AND COLUMN_NAME = 'hash_id');

SET @sql = IF(@exist = 0, 
    'ALTER TABLE orders ADD COLUMN hash_id VARCHAR(64) DEFAULT NULL AFTER status',
    'SELECT "hash_id ya existe" AS msg');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar índice único si no existe
SET @idx := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
             WHERE TABLE_SCHEMA = 'citrobbd' 
             AND TABLE_NAME = 'orders' 
             AND COLUMN_NAME = 'hash_id');

SET @sql2 = IF(@idx = 0, 
    'ALTER TABLE orders ADD UNIQUE INDEX idx_hash_id (hash_id)',
    'SELECT "idx_hash_id ya existe" AS msg');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
