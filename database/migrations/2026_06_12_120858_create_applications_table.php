<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE APPLICATIONS (
                APPLICATION_ID   NUMBER(10)    NOT NULL,
                INTERNSHIP_ID    NUMBER(10)    NOT NULL,
                STUDENT_ID       NUMBER(10)    NOT NULL,
                COVER_LETTER     VARCHAR2(2000) NULL,
                STATUS           VARCHAR2(20)  DEFAULT 'Pending' NOT NULL,
                APPLIED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_APPLICATIONS       PRIMARY KEY (APPLICATION_ID),
                CONSTRAINT UQ_APP_INTERN_STUDENT  UNIQUE (INTERNSHIP_ID, STUDENT_ID),
                CONSTRAINT FK_APP_INTERNSHIP      FOREIGN KEY (INTERNSHIP_ID)
                    REFERENCES INTERNSHIPS(INTERNSHIP_ID) ON DELETE CASCADE,
                CONSTRAINT FK_APP_STUDENT         FOREIGN KEY (STUDENT_ID)
                    REFERENCES STUDENTS(STUDENT_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_APP_STATUS         CHECK (
                    STATUS IN (
                        'Pending','Reviewed','Shortlisted',
                        'Interview','Accepted','Rejected'
                    )
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE APPLICATIONS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER APPLICATIONS_BIR
                BEFORE INSERT ON APPLICATIONS
                FOR EACH ROW
            BEGIN
                IF :NEW.APPLICATION_ID IS NULL THEN
                    SELECT APPLICATIONS_SEQ.NEXTVAL
                    INTO :NEW.APPLICATION_ID FROM DUAL;
                END IF;
                :NEW.APPLIED_AT  := CURRENT_TIMESTAMP;
                :NEW.UPDATED_AT  := CURRENT_TIMESTAMP;
            END;
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER APPLICATIONS_BUR
                BEFORE UPDATE ON APPLICATIONS
                FOR EACH ROW
            BEGIN
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS APPLICATIONS_BUR");
        DB::statement("DROP TRIGGER IF EXISTS APPLICATIONS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS APPLICATIONS_SEQ");
        DB::statement("DROP TABLE APPLICATIONS CASCADE CONSTRAINTS");
    }
};