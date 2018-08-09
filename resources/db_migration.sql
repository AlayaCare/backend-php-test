ALTER TABLE todos ADD COLUMN ( 
	todo_status enum('Pending','Completed') default 'Pending', 
	completed_date datetime DEFAULT NULL 
);