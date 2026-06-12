<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE INTERVIEWS (
                INTERVIEW_ID        NUMBER(10)    NOT NULL,
                APPLICATION_ID      NUMBER(10)    NOT NULL,
                SCHEDULED_DATE      DATE          NOT NULL,
                SCHEDULED_TIME      VARCHAR2(10)  NOT NULL,
                MODE                VARCHAR2(20)  NOT NULL,
                LOCATION_OR_LINK    VARCHAR2(200) NULL,
                NOTES               VARCHAR2(500) NULL,
                CREATED_AT          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_INTERVIEWS         PRIMARY KEY (INTERVIEW_ID),
                CONSTRAINT UQ_INTERVIEW_APP      UNIQUE (APPLICATION_ID),
                CONSTRAINT FK_INTERVIEW_APP      FOREIGN KEY (APPLICATION_ID)
                    REFERENCES APPLICATIONS(APPLICATION_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_INTERVIEW_MODE    CHECK (
                    MODE IN ('In-person','Video','Phone')
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE INTERVIEWS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER INTERVIEWS_BIR
                BEFORE INSERT ON INTERVIEWS
                FOR EACH ROW
            BEGIN
                IF :NEW.INTERVIEW_ID IS NULL THEN
                    SELECT INTERVIEWS_SEQ.NEXTVAL
                    INTO :NEW.INTERVIEW_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS INTERVIEWS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS INTERVIEWS_SEQ");
        DB::statement("DROP TABLE INTERVIEWS CASCADE CONSTRAINTS");
    }
};