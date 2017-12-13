-- Jeremy El bez
-- add new coloms in order to complete TASK-2
-- 2017-12-13


ALTER TABLE todos ADD status BIT NOT NULL DEFAULT b'0' AFTER description;
ALTER TABLE todos ADD statusDate DATETIME NULL AFTER status;