-- Admin Dashboard and Reporting System
-- View 1: Application pipeline summary per internship
CREATE OR REPLACE VIEW VW_APPLICATION_SUMMARY AS
SELECT
    i.INTERNSHIP_ID,
    i.TITLE,
    i.STATUS         AS INTERNSHIP_STATUS,
    c.COMPANY_NAME,
    COUNT(a.APPLICATION_ID)                                             AS TOTAL_APPLICATIONS,
    SUM(CASE WHEN a.STATUS = 'Pending'     THEN 1 ELSE 0 END)          AS PENDING,
    SUM(CASE WHEN a.STATUS = 'Reviewed'    THEN 1 ELSE 0 END)          AS REVIEWED,
    SUM(CASE WHEN a.STATUS = 'Shortlisted' THEN 1 ELSE 0 END)          AS SHORTLISTED,
    SUM(CASE WHEN a.STATUS = 'Interview'   THEN 1 ELSE 0 END)          AS INTERVIEW_COUNT,
    SUM(CASE WHEN a.STATUS = 'Accepted'    THEN 1 ELSE 0 END)          AS ACCEPTED,
    SUM(CASE WHEN a.STATUS = 'Rejected'    THEN 1 ELSE 0 END)          AS REJECTED
FROM INTERNSHIPS i
INNER JOIN COMPANIES c    ON i.COMPANY_ID    = c.COMPANY_ID
LEFT  JOIN APPLICATIONS a ON i.INTERNSHIP_ID = a.INTERNSHIP_ID
GROUP BY i.INTERNSHIP_ID, i.TITLE, i.STATUS, c.COMPANY_NAME;


-- View 2: Student placement report
CREATE OR REPLACE VIEW VW_STUDENT_PLACEMENT AS
SELECT
    s.STUDENT_ID,
    s.FIRST_NAME || ' ' || s.LAST_NAME                                  AS STUDENT_NAME,
    s.DEPARTMENT,
    s.UNIVERSITY,
    s.GPA,
    COUNT(a.APPLICATION_ID)                                              AS TOTAL_APPLIED,
    SUM(CASE WHEN a.STATUS = 'Accepted' THEN 1 ELSE 0 END)              AS PLACEMENTS,
    CASE
        WHEN COUNT(a.APPLICATION_ID) > 0
        THEN ROUND(
            SUM(CASE WHEN a.STATUS = 'Accepted' THEN 1 ELSE 0 END)
            / COUNT(a.APPLICATION_ID) * 100, 1)
        ELSE 0
    END                                                                  AS PLACEMENT_RATE_PCT
FROM STUDENTS s
LEFT JOIN APPLICATIONS a ON s.STUDENT_ID = a.STUDENT_ID
GROUP BY s.STUDENT_ID, s.FIRST_NAME, s.LAST_NAME,
         s.DEPARTMENT, s.UNIVERSITY, s.GPA;


-- View 3: Skill demand report
CREATE OR REPLACE VIEW VW_SKILL_DEMAND AS
SELECT
    sk.SKILL_ID,
    sk.SKILL_NAME,
    sk.CATEGORY,
    COUNT(DISTINCT ins.INTERNSHIP_ID)  AS REQUIRED_BY_INTERNSHIPS,
    COUNT(DISTINCT ss.STUDENT_ID)      AS STUDENTS_WITH_SKILL
FROM SKILLS sk
LEFT JOIN INTERNSHIP_SKILLS ins ON sk.SKILL_ID = ins.SKILL_ID
LEFT JOIN STUDENT_SKILLS    ss  ON sk.SKILL_ID = ss.SKILL_ID
GROUP BY sk.SKILL_ID, sk.SKILL_NAME, sk.CATEGORY
ORDER BY REQUIRED_BY_INTERNSHIPS DESC;





-- Procedures

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


-- Functions
CREATE OR REPLACE FUNCTION FN_GET_MATCH_SCORE (
    p_student_id    IN NUMBER,
    p_internship_id IN NUMBER
) RETURN NUMBER
AS
    v_score NUMBER(5,2) := 0;
BEGIN
    SELECT ROUND(
        COUNT(CASE WHEN ss.SKILL_ID IS NOT NULL THEN 1 END) * 100.0
        / NULLIF(COUNT(ins.SKILL_ID), 0),
    2)
    INTO v_score
    FROM INTERNSHIP_SKILLS ins
    LEFT JOIN STUDENT_SKILLS ss
        ON ins.SKILL_ID   = ss.SKILL_ID
        AND ss.STUDENT_ID = p_student_id
    WHERE ins.INTERNSHIP_ID = p_internship_id;

    RETURN NVL(v_score, 0);

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RETURN 0;
    WHEN OTHERS THEN
        RETURN -1;
END FN_GET_MATCH_SCORE;
/




CREATE OR REPLACE FUNCTION FN_GET_APPLICATION_COUNT (
    p_internship_id IN NUMBER
) RETURN NUMBER
AS
    v_count NUMBER := 0;
BEGIN
    SELECT COUNT(*)
    INTO v_count
    FROM APPLICATIONS
    WHERE INTERNSHIP_ID = p_internship_id;

    RETURN v_count;

EXCEPTION
    WHEN OTHERS THEN
        RETURN 0;
END FN_GET_APPLICATION_COUNT;
/





CREATE OR REPLACE FUNCTION FN_IS_PROFILE_COMPLETE (
    p_student_id IN NUMBER
) RETURN NUMBER
AS
    v_first_name      VARCHAR2(50);
    v_last_name       VARCHAR2(50);
    v_university      VARCHAR2(100);
    v_department      VARCHAR2(100);
    v_gpa             NUMBER;
    v_grad_year       NUMBER;
    v_cv_path         VARCHAR2(255);
    v_skill_count     NUMBER;
BEGIN
    SELECT FIRST_NAME, LAST_NAME, UNIVERSITY, DEPARTMENT,
           GPA, GRADUATION_YEAR, CV_FILE_PATH
    INTO v_first_name, v_last_name, v_university, v_department,
         v_gpa, v_grad_year, v_cv_path
    FROM STUDENTS
    WHERE STUDENT_ID = p_student_id;

    SELECT COUNT(*)
    INTO v_skill_count
    FROM STUDENT_SKILLS
    WHERE STUDENT_ID = p_student_id;

    IF v_first_name = 'New' OR v_last_name = 'Student'
        OR v_university = 'Not Set' OR v_department = 'Not Set'
        OR v_gpa IS NULL OR v_grad_year IS NULL
        OR v_cv_path IS NULL OR v_skill_count = 0
    THEN
        RETURN 0;
    END IF;

    RETURN 1;

EXCEPTION
    WHEN NO_DATA_FOUND THEN
        RETURN 0;
    WHEN OTHERS THEN
        RETURN 0;
END FN_IS_PROFILE_COMPLETE;
/


-- Triggers

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


-- Cursors
SET SERVEROUTPUT ON;
DECLARE
    -- Explicit cursor: students with their application and skill counts
    CURSOR c_student_report IS
        SELECT
            s.STUDENT_ID,
            s.FIRST_NAME || ' ' || s.LAST_NAME  AS STUDENT_NAME,
            s.DEPARTMENT,
            COUNT(DISTINCT a.APPLICATION_ID)     AS APP_COUNT,
            COUNT(DISTINCT ss.SKILL_ID)          AS SKILL_COUNT,
            FN_IS_PROFILE_COMPLETE(s.STUDENT_ID) AS PROFILE_COMPLETE
        FROM STUDENTS s
        LEFT JOIN APPLICATIONS  a  ON s.STUDENT_ID = a.STUDENT_ID
        LEFT JOIN STUDENT_SKILLS ss ON s.STUDENT_ID = ss.STUDENT_ID
        GROUP BY s.STUDENT_ID, s.FIRST_NAME, s.LAST_NAME, s.DEPARTMENT
        ORDER BY APP_COUNT DESC;

    -- Record type to hold each row
    v_student c_student_report%ROWTYPE;
    v_total   NUMBER := 0;

BEGIN
    DBMS_OUTPUT.PUT_LINE('======= STUDENT ACTIVITY REPORT =======');
    DBMS_OUTPUT.PUT_LINE(
        RPAD('Name', 30) ||
        RPAD('Dept', 20) ||
        RPAD('Apps', 6) ||
        RPAD('Skills', 8) ||
        'Profile'
    );
    DBMS_OUTPUT.PUT_LINE(RPAD('-', 70, '-'));

    OPEN c_student_report;

    LOOP
        FETCH c_student_report INTO v_student;
        EXIT WHEN c_student_report%NOTFOUND;

        DBMS_OUTPUT.PUT_LINE(
            RPAD(v_student.STUDENT_NAME, 30) ||
            RPAD(v_student.DEPARTMENT, 20) ||
            RPAD(v_student.APP_COUNT, 6) ||
            RPAD(v_student.SKILL_COUNT, 8) ||
            CASE v_student.PROFILE_COMPLETE
                WHEN 1 THEN 'Complete'
                ELSE 'Incomplete'
            END
        );

        v_total := v_total + 1;
    END LOOP;

    CLOSE c_student_report;

    DBMS_OUTPUT.PUT_LINE(RPAD('-', 70, '-'));
    DBMS_OUTPUT.PUT_LINE('Total students: ' || v_total);
    DBMS_OUTPUT.PUT_LINE('======= END OF REPORT =======');

EXCEPTION
    WHEN OTHERS THEN
        IF c_student_report%ISOPEN THEN
            CLOSE c_student_report;
        END IF;
        DBMS_OUTPUT.PUT_LINE('ERROR: ' || SQLERRM);
END;
/