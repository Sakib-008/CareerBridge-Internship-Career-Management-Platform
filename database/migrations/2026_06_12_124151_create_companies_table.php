<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE COMPANIES (
                COMPANY_ID      NUMBER(10)    NOT NULL,
                USER_ID         NUMBER(10)    NOT NULL,
                COMPANY_NAME    VARCHAR2(100) NOT NULL,
                INDUSTRY        VARCHAR2(50)  NOT NULL,
                COMPANY_SIZE    VARCHAR2(20)  NULL,
                LOCATION        VARCHAR2(100) NOT NULL,
                WEBSITE         VARCHAR2(100) NULL,
                DESCRIPTION     VARCHAR2(2000) NULL,
                CONTACT_PERSON  VARCHAR2(100) NULL,
                CONTACT_EMAIL   VARCHAR2(100) NULL,
                CREATED_AT      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                UPDATED_AT      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_COMPANIES       PRIMARY KEY (COMPANY_ID),
                CONSTRAINT UQ_COMPANIES_USER  UNIQUE (USER_ID),
                CONSTRAINT FK_COMPANIES_USER  FOREIGN KEY (USER_ID)
                    REFERENCES USERS(USER_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_COMPANY_SIZE   CHECK (
                    COMPANY_SIZE IS NULL OR
                    COMPANY_SIZE IN ('1-10','11-50','51-200','201-500','500+')
                )
            )
        ");

        DB::statement("
            CREATE SEQUENCE COMPANIES_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER COMPANIES_BIR
                BEFORE INSERT ON COMPANIES
                FOR EACH ROW
            BEGIN
                IF :NEW.COMPANY_ID IS NULL THEN
                    SELECT COMPANIES_SEQ.NEXTVAL INTO :NEW.COMPANY_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER COMPANIES_BUR
                BEFORE UPDATE ON COMPANIES
                FOR EACH ROW
            BEGIN
                :NEW.UPDATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS COMPANIES_BUR");
        DB::statement("DROP TRIGGER IF EXISTS COMPANIES_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS COMPANIES_SEQ");
        DB::statement("DROP TABLE COMPANIES CASCADE CONSTRAINTS");
    }
};