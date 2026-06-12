<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE STUDENT_SKILLS (
                STUDENT_SKILL_ID  NUMBER(10)   NOT NULL,
                STUDENT_ID        NUMBER(10)   NOT NULL,
                SKILL_ID          NUMBER(10)   NOT NULL,
                PROFICIENCY       VARCHAR2(20) NOT NULL,
                ADDED_AT          TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_STUDENT_SKILLS    PRIMARY KEY (STUDENT_SKILL_ID),
                CONSTRAINT UQ_STUDENT_SKILL     UNIQUE (STUDENT_ID, SKILL_ID),
                CONSTRAINT FK_SS_STUDENT        FOREIGN KEY (STUDENT_ID)
                    REFERENCES STUDENTS(STUDENT_ID) ON DELETE CASCADE,
                CONSTRAINT FK_SS_SKILL          FOREIGN KEY (SKILL_ID)
                    REFERENCES SKILLS(SKILL_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_SS_PROFICIENCY   CHECK (
                    PROFICIENCY IN ('Beginner','Intermediate','Advanced')
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE STUDENT_SKILLS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER STUDENT_SKILLS_BIR
                BEFORE INSERT ON STUDENT_SKILLS
                FOR EACH ROW
            BEGIN
                IF :NEW.STUDENT_SKILL_ID IS NULL THEN
                    SELECT STUDENT_SKILLS_SEQ.NEXTVAL
                    INTO :NEW.STUDENT_SKILL_ID FROM DUAL;
                END IF;
                :NEW.ADDED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS STUDENT_SKILLS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS STUDENT_SKILLS_SEQ");
        DB::statement("DROP TABLE STUDENT_SKILLS CASCADE CONSTRAINTS");
    }
};