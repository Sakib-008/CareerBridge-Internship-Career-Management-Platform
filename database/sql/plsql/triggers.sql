CREATE OR REPLACE TRIGGER TRG_AUDIT_APPLICATION
    AFTER UPDATE OF STATUS ON APPLICATIONS
    FOR EACH ROW
BEGIN
    IF :OLD.STATUS != :NEW.STATUS THEN
        INSERT INTO AUDIT_LOG (
            USER_ID,
            ACTION,
            TABLE_NAME,
            RECORD_ID,
            OLD_VALUE,
            NEW_VALUE
        )
        VALUES (
            NULL,
            'STATUS_CHANGE',
            'APPLICATIONS',
            :NEW.APPLICATION_ID,
            :OLD.STATUS,
            :NEW.STATUS
        );
    END IF;
END TRG_AUDIT_APPLICATION;
/




CREATE OR REPLACE TRIGGER TRG_NO_DUPLICATE_APPLICATION
    BEFORE INSERT ON APPLICATIONS
    FOR EACH ROW
DECLARE
    v_count NUMBER;
BEGIN
    SELECT COUNT(*)
    INTO v_count
    FROM APPLICATIONS
    WHERE STUDENT_ID    = :NEW.STUDENT_ID
    AND   INTERNSHIP_ID = :NEW.INTERNSHIP_ID;

    IF v_count > 0 THEN
        RAISE_APPLICATION_ERROR(
            -20001,
            'Student has already applied to this internship.'
        );
    END IF;
END TRG_NO_DUPLICATE_APPLICATION;
/


CREATE OR REPLACE TRIGGER TRG_AUTO_NOTIFY_ACCEPTED
    AFTER UPDATE OF STATUS ON APPLICATIONS
    FOR EACH ROW
DECLARE
    v_user_id          NUMBER;
    v_internship_title VARCHAR2(150);
BEGIN
    IF :NEW.STATUS = 'Accepted' AND :OLD.STATUS != 'Accepted' THEN

        SELECT u.USER_ID
        INTO v_user_id
        FROM STUDENTS s
        INNER JOIN USERS u ON s.USER_ID = u.USER_ID
        WHERE s.STUDENT_ID = :NEW.STUDENT_ID;

        SELECT TITLE
        INTO v_internship_title
        FROM INTERNSHIPS
        WHERE INTERNSHIP_ID = :NEW.INTERNSHIP_ID;

        INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE)
        VALUES (
            v_user_id,
            'Congratulations! Your application for "' ||
            v_internship_title || '" has been accepted!'
        );

    END IF;
EXCEPTION
    WHEN OTHERS THEN
        NULL; -- Don't block the UPDATE if notification fails
END TRG_AUTO_NOTIFY_ACCEPTED;
/

