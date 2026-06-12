<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE AUDIT_LOG (
                LOG_ID       NUMBER(10)    NOT NULL,
                USER_ID      NUMBER(10)    NULL,
                ACTION       VARCHAR2(100) NOT NULL,
                TABLE_NAME   VARCHAR2(50)  NOT NULL,
                RECORD_ID    NUMBER(10)    NULL,
                OLD_VALUE    VARCHAR2(500) NULL,
                NEW_VALUE    VARCHAR2(500) NULL,
                ACTION_TIME  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_AUDIT_LOG      PRIMARY KEY (LOG_ID),
                CONSTRAINT FK_AUDIT_USER     FOREIGN KEY (USER_ID)
                    REFERENCES USERS(USER_ID) ON DELETE SET NULL
            )
        ");

        DB::statement("
            CREATE SEQUENCE AUDIT_LOG_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER AUDIT_LOG_BIR
                BEFORE INSERT ON AUDIT_LOG
                FOR EACH ROW
            BEGIN
                IF :NEW.LOG_ID IS NULL THEN
                    SELECT AUDIT_LOG_SEQ.NEXTVAL INTO :NEW.LOG_ID FROM DUAL;
                END IF;
                :NEW.ACTION_TIME := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS AUDIT_LOG_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS AUDIT_LOG_SEQ");
        DB::statement("DROP TABLE AUDIT_LOG CASCADE CONSTRAINTS");
    }
};