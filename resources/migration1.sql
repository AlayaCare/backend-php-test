alter table todos add is_complete tinyint(1) unsigned default 0;
alter table todos add key(is_complete);
