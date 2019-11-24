/* remove completed field from todos
ALTER TABLE todos DROP completed
*/

/* add completed field into todos */
ALTER TABLE todos ADD completed BOOLEAN DEFAULT 0