-- DROP TABLE naixframe.users;
CREATE TABLE naixframe.users
(
  id integer NOT NULL DEFAULT nextval('naixframe.logins_id_seq'::regclass),
  "FirstName" character varying(100),
  "LastName" character varying(100),
  "UserName" character varying(100) NOT NULL,
  "UserEmail" character varying(254) NOT NULL,
  CONSTRAINT "users-id" PRIMARY KEY (id)
)
WITH ( OIDS=FALSE );
ALTER TABLE naixframe.users OWNER TO postgres;