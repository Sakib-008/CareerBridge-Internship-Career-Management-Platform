CREATE OR REPLACE PROCEDURE SP_UPDATE_APPLICATION_STATUS (
    p_application_id IN  NUMBER,
    p_new_status     IN  VARCHAR2,
    p_changed_by     IN  NUMBER,
    p_result         OUT VARCHAR2
)
AS
    v_old_status     VARCHAR2(20);
    v_student_user   NUMBER;
    v_internship_title VARCHAR2(150);
    v_count          NUMBER;

BEGIN
    -- Validate application exists
    SELECT COUNT(*)
    INTO v_count
    FROM APPLICATIONS
    WHERE APPLICATION_ID = p_application_id;

    IF v_count = 0 THEN
        p_result := 'ERROR: Application not found.';
        RETURN;
    END IF;

    -- Validate status value
    IF p_new_status NOT IN (
        'Pending','Reviewed','Shortlisted','Interview','Accepted','Rejected'
    ) THEN
        p_result := 'ERROR: Invalid status value.';
        RETURN;
    END IF;

    -- Get current status and internship title
    SELECT a.STATUS, i.TITLE
    INTO v_old_status, v_internship_title
    FROM APPLICATIONS a
    INNER JOIN INTERNSHIPS i ON a.INTERNSHIP_ID = i.INTERNSHIP_ID
    WHERE a.APPLICATION_ID = p_application_id;

    -- Get student's USER_ID for notification
    SELECT u.USER_ID
    INTO v_student_user
    FROM USERS u
    INNER JOIN STUDENTS s ON u.USER_ID = s.USER_ID
    INNER JOIN APPLICATIONS a ON s.STUDENT_ID = a.STUDENT_ID
    WHERE a.APPLICATION_ID = p_application_id;

    -- Update the application status
    UPDATE APPLICATIONS
    SET STATUS = p_new_status
    WHERE APPLICATION_ID = p_application_id;

    -- Insert notification for the student
    INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE)
    VALUES (
        v_student_user,
        'Your application for "' || v_internship_title ||
        '" status changed from ' || v_old_status ||
        ' to ' || p_new_status || '.'
    );

    -- Log to AUDIT_LOG
    INSERT INTO AUDIT_LOG (
        USER_ID, ACTION, TABLE_NAME,
        RECORD_ID, OLD_VALUE, NEW_VALUE
    )
    VALUES (
        p_changed_by,
        'UPDATE_STATUS',
        'APPLICATIONS',
        p_application_id,
        v_old_status,
        p_new_status
    );

    COMMIT;
    p_result := 'SUCCESS';

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_result := 'ERROR: ' || SQLERRM;
END SP_UPDATE_APPLICATION_STATUS;
/




CREATE OR REPLACE PROCEDURE SP_GENERATE_RECOMMENDATIONS (
    p_student_id IN NUMBER DEFAULT NULL,
    p_result     OUT VARCHAR2
)
AS
    -- Cursor: fetch all students (or one specific student)
    CURSOR c_students IS
        SELECT STUDENT_ID
        FROM STUDENTS
        WHERE (p_student_id IS NULL OR STUDENT_ID = p_student_id);

    v_match_score   NUMBER(5,2);
    v_exists_count  NUMBER;
    v_total         NUMBER := 0;

BEGIN
    -- Loop through each student
    FOR student_rec IN c_students LOOP

        -- For each open internship, calculate match score
        FOR internship_rec IN (
            SELECT
                i.INTERNSHIP_ID,
                ROUND(
                    COUNT(CASE WHEN ss.SKILL_ID IS NOT NULL THEN 1 END) * 100.0
                    / NULLIF(COUNT(ins.SKILL_ID), 0),
                2) AS MATCH_SCORE
            FROM INTERNSHIPS i
            INNER JOIN INTERNSHIP_SKILLS ins
                ON i.INTERNSHIP_ID = ins.INTERNSHIP_ID
            LEFT JOIN STUDENT_SKILLS ss
                ON ins.SKILL_ID   = ss.SKILL_ID
                AND ss.STUDENT_ID = student_rec.STUDENT_ID
            WHERE i.STATUS = 'Open'
            AND i.APPLICATION_DEADLINE >= TRUNC(SYSDATE)
            GROUP BY i.INTERNSHIP_ID
            HAVING ROUND(
                COUNT(CASE WHEN ss.SKILL_ID IS NOT NULL THEN 1 END) * 100.0
                / NULLIF(COUNT(ins.SKILL_ID), 0),
            2) > 0
        ) LOOP

            v_match_score := internship_rec.MATCH_SCORE;

            -- Check if recommendation already exists
            SELECT COUNT(*)
            INTO v_exists_count
            FROM RECOMMENDATIONS
            WHERE STUDENT_ID    = student_rec.STUDENT_ID
            AND   INTERNSHIP_ID = internship_rec.INTERNSHIP_ID;

            IF v_exists_count > 0 THEN
                -- Update existing
                UPDATE RECOMMENDATIONS
                SET MATCH_SCORE  = v_match_score,
                    GENERATED_AT = CURRENT_TIMESTAMP
                WHERE STUDENT_ID    = student_rec.STUDENT_ID
                AND   INTERNSHIP_ID = internship_rec.INTERNSHIP_ID;
            ELSE
                -- Insert new
                INSERT INTO RECOMMENDATIONS
                    (STUDENT_ID, INTERNSHIP_ID, MATCH_SCORE)
                VALUES
                    (student_rec.STUDENT_ID, internship_rec.INTERNSHIP_ID, v_match_score);
            END IF;

            v_total := v_total + 1;

        END LOOP;

    END LOOP;

    COMMIT;
    p_result := 'SUCCESS: ' || v_total || ' recommendations processed.';

EXCEPTION
    WHEN OTHERS THEN
        ROLLBACK;
        p_result := 'ERROR: ' || SQLERRM;
END SP_GENERATE_RECOMMENDATIONS;
/
