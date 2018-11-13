ALTER TABLE todos ADD completed TINYINT(1);

UPDATE todos SET completed=0;

ALTER TABLE todos MODIFY completed TINYINT(1) NOT NULL;
