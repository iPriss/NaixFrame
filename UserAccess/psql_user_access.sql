-- DROP SEQUENCE naixframe.users_account_id_seq;
CREATE SEQUENCE naixframe.users_account_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE naixframe.users_account_id_seq
  OWNER TO postgres;

-- DROP TABLE naixframe.users_account;
CREATE TABLE naixframe.users_account
(
  id integer NOT NULL DEFAULT nextval('naixframe.users_account_id_seq'::regclass),
  "first_name" character varying(100),
  "last_name" character varying(100),
  "user_name" character varying(100) NOT NULL,
  "user_email" character varying(254) NOT NULL,
  "user_status" character varying(50) NOT NULL,
  "registration_time" timestamp with time zone,
  "email_confirmation_token" character varying(100),
  "password_reminder_token" character varying(100),
  "password_reminder_expire" timestamp with time zone,
  CONSTRAINT "users-id" PRIMARY KEY (id)
)
WITH ( OIDS=FALSE );
ALTER TABLE naixframe.users_account OWNER TO postgres;

-- DROP SEQUENCE naixframe.logins_id_seq;
CREATE SEQUENCE naixframe.logins_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE naixframe.logins_id_seq
  OWNER TO postgres;

-- DROP TABLE naixframe.logins;
CREATE TABLE naixframe.logins
(
  id integer NOT NULL DEFAULT nextval('naixframe.logins_id_seq'::regclass),
  "user_name" character varying(100),
  "user_email" character varying(254),
  "password_salt" character varying(50),
  "password_hash" character varying(200),
  "related_user_id" integer NOT NULL,
  CONSTRAINT "logins-id" PRIMARY KEY (id),
  CONSTRAINT "logins-user-id" FOREIGN KEY ("related_user_id")
      REFERENCES naixframe.users_account (id) MATCH FULL
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH ( OIDS=FALSE );
ALTER TABLE naixframe.logins OWNER TO postgres;