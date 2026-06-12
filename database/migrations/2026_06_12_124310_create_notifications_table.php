<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE NOTIFICATIONS (
                NOTIFICATION_ID  NUMBER(10)    NOT NULL,
                USER_ID          NUMBER(10)    NOT NULL,
                MESSAGE          VARCHAR2(500) NOT NULL,
                IS_READ          NUMBER(1)     DEFAULT 0,
                CREATED_AT       TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT PK_NOTIFICATIONS     PRIMARY KEY (NOTIFICATION_ID),
                CONSTRAINT FK_NOTIF_USER        FOREIGN KEY (USER_ID)
                    REFERENCES USERS(USER_ID) ON DELETE CASCADE,
                CONSTRAINT CHK_NOTIF_READ       CHECK (IS_READ IN (0,1))
            )
        ");

        DB::statement("
            CREATE SEQUENCE NOTIFICATIONS_SEQ
                START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE
        ");

        DB::statement("
            CREATE OR REPLACE TRIGGER NOTIFICATIONS_BIR
                BEFORE INSERT ON NOTIFICATIONS
                FOR EACH ROW
            BEGIN
                IF :NEW.NOTIFICATION_ID IS NULL THEN
                    SELECT NOTIFICATIONS_SEQ.NEXTVAL
                    INTO :NEW.NOTIFICATION_ID FROM DUAL;
                END IF;
                :NEW.CREATED_AT := CURRENT_TIMESTAMP;
            END;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS NOTIFICATIONS_BIR");
        DB::statement("DROP SEQUENCE IF EXISTS NOTIFICATIONS_SEQ");
        DB::statement("DROP TABLE NOTIFICATIONS CASCADE CONSTRAINTS");
    }
};