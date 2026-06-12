<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE INTERNSHIP_SKILLS (
                INTERNSHIP_SKILL_ID  NUMBER(10)  NOT NULL,
                INTERNSHIP_ID        NUMBER(10)  NOT NULL,
                SKILL_ID             NUMBER(10)  NOT NULL,
                IS_MANDATORY         NUMBER(1)   DEFAULT 1,
                CONSTRAINT PK_INTERNSHIP_SKILLS   PRIMARY KEY (INTERNSHIP_SKILL_ID),
                CONSTRAINT UQ_INTERN_SKILL        UNIQUE (INTERNSHIP_ID, SKILL_ID),
                CONSTRAINT FK_IS_INTERNSHIP       FOREIGN KEY (INTERNSHIP_ID)
                    REFERENCES INTERNSHIPS(INTERNSHIP_ID) ON DELETE CASCADE,
                CONSTRAINT FK_IS_SKILL            FOREIGN KEY (SKILL_ID)
                    REFERENCES SKILLS(SKILL_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_IS_MANDATORY       CHECK (IS_MANDATORY IN (0,1))
            )
        ");

        DB::statement("
            CREATE SEQUENCE INTERNSHIP_SKILLS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER INTERNSHIP_SKILLS_BIR
                BEFORE INSERT ON INTERNSHIP_SKILLS
                FOR EACH ROW
            BEGIN
                IF :NEW.INTERNSHIP_SKILL_ID IS NULL THEN
                    SELECT INTERNSHIP_SKILLS_SEQ.NEXTVAL
                    INTO :NEW.INTERNSHIP_SKILL_ID FROM DUAL;
                END IF;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS INTERNSHIP_SKILLS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS INTERNSHIP_SKILLS_SEQ");
        DB::statement("DROP TABLE INTERNSHIP_SKILLS CASCADE CONSTRAINTS");
    }
};