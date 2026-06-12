<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE INTERNSHIPS (
                INTERNSHIP_ID          NUMBER(10)    NOT NULL,
                COMPANY_ID             NUMBER(10)    NOT NULL,
                TITLE                  VARCHAR2(150) NOT NULL,
                DESCRIPTION            VARCHAR2(3000) NOT NULL,
                LOCATION               VARCHAR2(100) NOT NULL,
                INTERNSHIP_TYPE        VARCHAR2(20)  NOT NULL,
                DURATION_MONTHS        NUMBER(2)     NOT NULL,
                STIPEND                NUMBER(10,2)  DEFAULT 0,
                VACANCIES              NUMBER(3)     NOT NULL,
                APPLICATION_DEADLINE   DATE          NOT NULL,
                STATUS                 VARCHAR2(10)  DEFAULT 'Open' NOT NULL,
                CREATED_AT             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT             TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_INTERNSHIPS        PRIMARY KEY (INTERNSHIP_ID),
                CONSTRAINT FK_INTERN_COMPANY     FOREIGN KEY (COMPANY_ID)
                    REFERENCES COMPANIES(COMPANY_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_INTERN_TYPE       CHECK (
                    INTERNSHIP_TYPE IN ('Remote','On-site','Hybrid')
                ),
                CONSTRAINT CHK_INTERN_DURATION   CHECK (
                    DURATION_MONTHS BETWEEN 1 AND 24
                ),
                CONSTRAINT CHK_INTERN_STIPEND    CHECK (STIPEND >= 0),
                CONSTRAINT CHK_INTERN_VACANCIES  CHECK (VACANCIES > 0),
                CONSTRAINT CHK_INTERN_STATUS     CHECK (
                    STATUS IN ('Open','Closed','Paused')
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE INTERNSHIPS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER INTERNSHIPS_BIR
                BEFORE INSERT ON INTERNSHIPS
                FOR EACH ROW
            BEGIN
                IF :NEW.INTERNSHIP_ID IS NULL THEN
                    SELECT INTERNSHIPS_SEQ.NEXTVAL
                    INTO :NEW.INTERNSHIP_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER INTERNSHIPS_BUR
                BEFORE UPDATE ON INTERNSHIPS
                FOR EACH ROW
            BEGIN
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS INTERNSHIPS_BUR");
        DB::statement("DROP TRIGGER IF EXISTS INTERNSHIPS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS INTERNSHIPS_SEQ");
        DB::statement("DROP TABLE INTERNSHIPS CASCADE CONSTRAINTS");
    }
};