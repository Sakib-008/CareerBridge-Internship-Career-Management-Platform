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