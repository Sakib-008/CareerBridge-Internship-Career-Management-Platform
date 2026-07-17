-- Connect as SYSDBA first
CREATE USER careerbridge IDENTIFIED BY 2022;

GRANT CONNECT, RESOURCE, DBA TO careerbridge;
GRANT CREATE SESSION TO careerbridge;
GRANT UNLIMITED TABLESPACE TO careerbridge;
GRANT CREATE VIEW TO careerbridge;
GRANT CREATE SEQUENCE TO careerbridge;
GRANT CREATE TRIGGER TO careerbridge;
GRANT CREATE PROCEDURE TO careerbridge;


-- To make migration files ... Rest in databse/migrations

