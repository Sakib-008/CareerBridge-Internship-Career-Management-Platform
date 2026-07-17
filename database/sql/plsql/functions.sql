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
