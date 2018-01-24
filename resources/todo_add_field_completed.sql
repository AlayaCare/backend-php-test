ALTER TABLE todos 
ADD COLUMN completed tinyint NULL DEFAULT 0 AFTER description;