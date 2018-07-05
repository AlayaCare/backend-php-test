ALTER TABLE todos ADD COLUMN (
  completed int(1) COMMENT '0:not completed, 1:completed' DEFAULT 0,
  date_completed datetime DEFAULT NULL
);