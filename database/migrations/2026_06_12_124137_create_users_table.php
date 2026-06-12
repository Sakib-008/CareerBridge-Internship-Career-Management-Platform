<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE USERS (
                USER_ID       NUMBER(10)    NOT NULL,
                EMAIL         VARCHAR2(100) NOT NULL,
                PASSWORD_HASH VARCHAR2(255) NOT NULL,
                ROLE          VARCHAR2(20)  NOT NULL,
                IS_ACTIVE     NUMBER(1)     DEFAULT 1 NOT NULL,
                CREATED_AT    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_USERS        PRIMARY KEY (USER_ID),
                CONSTRAINT UQ_USERS_EMAIL  UNIQUE (EMAIL),
                CONSTRAINT CHK_USERS_ROLE CHECK (ROLE IN ('student','company','admin')),
                CONSTRAINT CHK_USERS_ACTIVE CHECK (IS_ACTIVE IN (0,1))
            )
        ");

        DB::statement("
            CREATE SEQUENCE USERS_SEQ
                START WITH 1
                INCREMENT BY 1
                NOCACHE
                NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER USERS_BIR
                BEFORE INSERT ON USERS
                FOR EACH ROW
            BEGIN
                IF :NEW.USER_ID IS NULL THEN
                    SELECT USERS_SEQ.NEXTVAL INTO :NEW.USER_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER USERS_BUR
                BEFORE UPDATE ON USERS
                FOR EACH ROW
            BEGIN
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS USERS_BUR");
        DB::statement("DROP TRIGGER IF EXISTS USERS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS USERS_SEQ");
        DB::statement("DROP TABLE USERS CASCADE CONSTRAINTS");
    }
};