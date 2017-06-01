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
  "FirstName" character varying(100),
  "LastName" character varying(100),
  "UserName" character varying(100) NOT NULL,
  "UserEmail" character varying(254) NOT NULL,
  "UserStatus" character varying(50) NOT NULL,
  "RegistrationTime" timestamp with time zone,
  "EmailConfirmationToken" character varying(100),
  "PasswordReminderToken" character varying(100),
  "PasswordReminderExpire" timestamp with time zone,
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
  "UserName" character varying(100),
  "UserEmail" character varying(254),
  "PasswordSalt" character varying(50),
  "PasswordHash" character varying(200),
  "RelatedUserID" integer NOT NULL,
  CONSTRAINT "logins-id" PRIMARY KEY (id),
  CONSTRAINT "logins-user-id" FOREIGN KEY ("RelatedUserID")
      REFERENCES naixframe.users_account (id) MATCH FULL
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH ( OIDS=FALSE );
ALTER TABLE naixframe.logins OWNER TO postgres;