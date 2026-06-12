<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE SKILLS (
                SKILL_ID    NUMBER(10)    NOT NULL,
                SKILL_NAME  VARCHAR2(100) NOT NULL,
                CATEGORY    VARCHAR2(50)  NOT NULL,
                CREATED_AT  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_SKILLS        PRIMARY KEY (SKILL_ID),
                CONSTRAINT UQ_SKILLS_NAME   UNIQUE (SKILL_NAME)
            )
        ");

        DB::statement("
            CREATE SEQUENCE SKILLS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER SKILLS_BIR
                BEFORE INSERT ON SKILLS
                FOR EACH ROW
            BEGIN
                IF :NEW.SKILL_ID IS NULL THEN
                    SELECT SKILLS_SEQ.NEXTVAL INTO :NEW.SKILL_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS SKILLS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS SKILLS_SEQ");
        DB::statement("DROP TABLE SKILLS CASCADE CONSTRAINTS");
    }
};