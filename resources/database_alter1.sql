ALTER TABLE todos
    ADD completed
        BOOLEAN
        NOT NULL
        DEFAULT FALSE
        AFTER user_id;