ALTER TABLE todos ADD COLUMN completed INTEGER DEFAULT 0;
UPDATE todos SET completed = 0;