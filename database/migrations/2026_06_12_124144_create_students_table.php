<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE STUDENTS (
                STUDENT_ID       NUMBER(10)    NOT NULL,
                USER_ID          NUMBER(10)    NOT NULL,
                FIRST_NAME       VARCHAR2(50)  NOT NULL,
                LAST_NAME        VARCHAR2(50)  NOT NULL,
                PHONE            VARCHAR2(20)  NULL,
                DATE_OF_BIRTH    DATE          NULL,
                UNIVERSITY       VARCHAR2(100) NOT NULL,
                DEPARTMENT       VARCHAR2(100) NOT NULL,
                GPA              NUMBER(3,2)   NULL,
                GRADUATION_YEAR  NUMBER(4)     NULL,
                CV_FILE_PATH     VARCHAR2(255) NULL,
                PROFILE_SUMMARY  VARCHAR2(1000) NULL,
                CREATED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_STUDENTS         PRIMARY KEY (STUDENT_ID),
                CONSTRAINT UQ_STUDENTS_USER    UNIQUE (USER_ID),
                CONSTRAINT FK_STUDENTS_USER    FOREIGN KEY (USER_ID)
                    REFERENCES USERS(USER_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_STUDENTS_GPA
                    CHECK (GPA IS NULL OR GPA BETWEEN 0.0 AND 4.0),
                CONSTRAINT CHK_STUDENTS_GRADYR
                    CHECK (GRADUATION_YEAR IS NULL OR GRADUATION_YEAR >= 2000)
            )
        ");

        DB::statement("
            CREATE SEQUENCE STUDENTS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER STUDENTS_BIR
                BEFORE INSERT ON STUDENTS
                FOR EACH ROW
            BEGIN
                IF :NEW.STUDENT_ID IS NULL THEN
                    SELECT STUDENTS_SEQ.NEXTVAL INTO :NEW.STUDENT_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER STUDENTS_BUR
                BEFORE UPDATE ON STUDENTS
                FOR EACH ROW
            BEGIN
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS STUDENTS_BUR");
        DB::statement("DROP TRIGGER IF EXISTS STUDENTS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS STUDENTS_SEQ");
        DB::statement("DROP TABLE STUDENTS CASCADE CONSTRAINTS");
    }
};