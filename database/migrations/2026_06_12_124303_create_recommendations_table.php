<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE RECOMMENDATIONS (
                RECOMMENDATION_ID  NUMBER(10)    NOT NULL,
                STUDENT_ID         NUMBER(10)    NOT NULL,
                INTERNSHIP_ID      NUMBER(10)    NOT NULL,
                MATCH_SCORE        NUMBER(5,2)   NOT NULL,
                GENERATED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_RECOMMENDATIONS    PRIMARY KEY (RECOMMENDATION_ID),
                CONSTRAINT UQ_REC_STUDENT_INTERN UNIQUE (STUDENT_ID, INTERNSHIP_ID),
                CONSTRAINT FK_REC_STUDENT        FOREIGN KEY (STUDENT_ID)
                    REFERENCES STUDENTS(STUDENT_ID) ON DELETE CASCADE,
                CONSTRAINT FK_REC_INTERNSHIP     FOREIGN KEY (INTERNSHIP_ID)
                    REFERENCES INTERNSHIPS(INTERNSHIP_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_REC_SCORE         CHECK (
                    MATCH_SCORE BETWEEN 0 AND 100
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE RECOMMENDATIONS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER RECOMMENDATIONS_BIR
                BEFORE INSERT ON RECOMMENDATIONS
                FOR EACH ROW
            BEGIN
                IF :NEW.RECOMMENDATION_ID IS NULL THEN
                    SELECT RECOMMENDATIONS_SEQ.NEXTVAL
                    INTO :NEW.RECOMMENDATION_ID FROM DUAL;
                END IF;
                :NEW.GENERATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS RECOMMENDATIONS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS RECOMMENDATIONS_SEQ");
        DB::statement("DROP TABLE RECOMMENDATIONS CASCADE CONSTRAINTS");
    }
};