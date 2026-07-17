<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding demo data...');

        // ── USERS ──────────────────────────────────────────────────────
        // 3 Students
        $student_users = [];
        $students_data = [
            ['sakib@student.com',   'Sakib',   'Ahmed',    'KUET',  'CSE',              3.85, 2025],
            ['priya@student.com',   'Priya',   'Sharma',   'BUET',  'EEE',              3.72, 2025],
            ['rafiq@student.com',   'Rafiq',   'Islam',    'DU',    'Computer Science', 3.60, 2026],
        ];

        foreach ($students_data as $s) {
            DB::insert(
                "INSERT INTO USERS (EMAIL, PASSWORD_HASH, ROLE, IS_ACTIVE)
                 VALUES (:email, :password_hash, 'student', 1)",
                ['email' => $s[0], 'password_hash' => Hash::make('Password123')]
            );
            $uid = DB::select(
                "SELECT USER_ID FROM USERS WHERE EMAIL = :email AND ROWNUM = 1",
                ['email' => $s[0]]
            )[0]->user_id;
            $student_users[] = $uid;

            DB::insert(
                "INSERT INTO STUDENTS
                    (USER_ID, FIRST_NAME, LAST_NAME, UNIVERSITY, DEPARTMENT,
                     GPA, GRADUATION_YEAR, PHONE, PROFILE_SUMMARY)
                 VALUES
                    (:user_id, :first, :last, :uni, :dept, :gpa, :grad, :phone, :summary)",
                [
                    'user_id' => $uid,
                    'first'   => $s[1],
                    'last'    => $s[2],
                    'uni'     => $s[3],
                    'dept'    => $s[4],
                    'gpa'     => $s[5],
                    'grad'    => $s[6],
                    'phone'   => '017' . rand(10000000, 99999999),
                    'summary' => "Final year {$s[4]} student passionate about software development and data science.",
                ]
            );
        }

        // 2 Companies
        $company_users = [];
        $companies_data = [
            ['hr@techcorp.com',    'TechCorp Solutions',  'Software Development', '51-200',   'Dhaka, Bangladesh',  'https://techcorp.example.com',  'Leading software company building web and mobile solutions.'],
            ['hr@dataworks.com',   'DataWorks Analytics', 'Data Science',         '11-50',    'Chittagong, Bangladesh', 'https://dataworks.example.com', 'Data analytics firm specializing in ML and business intelligence.'],
        ];

        foreach ($companies_data as $c) {
            DB::insert(
                "INSERT INTO USERS (EMAIL, PASSWORD_HASH, ROLE, IS_ACTIVE)
                 VALUES (:email, :password_hash, 'company', 1)",
                ['email' => $c[0], 'password_hash' => Hash::make('Password123')]
            );
            $uid = DB::select(
                "SELECT USER_ID FROM USERS WHERE EMAIL = :email AND ROWNUM = 1",
                ['email' => $c[0]]
            )[0]->user_id;
            $company_users[] = $uid;

            DB::insert(
                "INSERT INTO COMPANIES
                    (USER_ID, COMPANY_NAME, INDUSTRY, COMPANY_SIZE, LOCATION,
                     WEBSITE, DESCRIPTION, CONTACT_PERSON, CONTACT_EMAIL)
                 VALUES
                    (:user_id, :name, :industry, :size, :location,
                     :website, :description, :contact_person, :contact_email)",
                [
                    'user_id'        => $uid,
                    'name'           => $c[1],
                    'industry'       => $c[2],
                    'size'           => $c[3],
                    'location'       => $c[4],
                    'website'        => $c[5],
                    'description'    => $c[6],
                    'contact_person' => 'HR Manager',
                    'contact_email'  => $c[0],
                ]
            );
        }

        // Get company IDs
        $companyIds = [];
        foreach ($company_users as $uid) {
            $row = DB::select(
                "SELECT COMPANY_ID FROM COMPANIES WHERE USER_ID = :user_id AND ROWNUM = 1",
                ['user_id' => $uid]
            );
            $companyIds[] = $row[0]->company_id;
        }

        // Get student IDs
        $studentIds = [];
        foreach ($student_users as $uid) {
            $row = DB::select(
                "SELECT STUDENT_ID FROM STUDENTS WHERE USER_ID = :user_id AND ROWNUM = 1",
                ['user_id' => $uid]
            );
            $studentIds[] = $row[0]->student_id;
        }

        $this->command->info('Users and profiles seeded.');

        // ── SKILLS: assign to students ─────────────────────────────────
        // Get skill IDs by name
        $getSkillId = function (string $name): int {
            $row = DB::select(
                "SELECT SKILL_ID FROM SKILLS WHERE SKILL_NAME = :name AND ROWNUM = 1",
                ['name' => $name]
            );
            return empty($row) ? 0 : (int) $row[0]->skill_id;
        };

        $studentSkills = [
            // Sakib: Python, SQL, Laravel, Git, Problem Solving
            $studentIds[0] => [
                [$getSkillId('Python'),          'Advanced'],
                [$getSkillId('SQL'),             'Advanced'],
                [$getSkillId('Laravel'),         'Intermediate'],
                [$getSkillId('Git'),             'Intermediate'],
                [$getSkillId('Problem Solving'), 'Advanced'],
            ],
            // Priya: Machine Learning, Python, Data Analysis, Excel, Tableau
            $studentIds[1] => [
                [$getSkillId('Machine Learning'), 'Intermediate'],
                [$getSkillId('Python'),           'Advanced'],
                [$getSkillId('Data Analysis'),    'Advanced'],
                [$getSkillId('Excel'),            'Intermediate'],
                [$getSkillId('Tableau'),          'Beginner'],
            ],
            // Rafiq: JavaScript, React, HTML/CSS, Git, Communication
            $studentIds[2] => [
                [$getSkillId('JavaScript'),   'Advanced'],
                [$getSkillId('React'),        'Intermediate'],
                [$getSkillId('HTML/CSS'),     'Advanced'],
                [$getSkillId('Git'),          'Beginner'],
                [$getSkillId('Communication'),'Intermediate'],
            ],
        ];

        foreach ($studentSkills as $studentId => $skills) {
            foreach ($skills as [$skillId, $proficiency]) {
                if ($skillId === 0) continue;
                DB::insert(
                    "INSERT INTO STUDENT_SKILLS (STUDENT_ID, SKILL_ID, PROFICIENCY)
                     VALUES (:student_id, :skill_id, :proficiency)",
                    [
                        'student_id'  => $studentId,
                        'skill_id'    => $skillId,
                        'proficiency' => $proficiency,
                    ]
                );
            }
        }

        $this->command->info('Student skills seeded.');

        // ── INTERNSHIPS ────────────────────────────────────────────────
        $internships = [
            // TechCorp internships
            [
                'company_id'   => $companyIds[0],
                'title'        => 'Backend Developer Intern',
                'description'  => 'Work on Laravel-based REST APIs and Oracle database systems. You will build scalable backend features and write clean, tested code.',
                'location'     => 'Dhaka, Bangladesh',
                'type'         => 'On-site',
                'duration'     => 3,
                'stipend'      => 15000,
                'vacancies'    => 2,
                'deadline'     => date('Y-m-d', strtotime('+60 days')),
                'status'       => 'Open',
                'skills'       => [
                    [$getSkillId('PHP'),    1],
                    [$getSkillId('Laravel'),1],
                    [$getSkillId('SQL'),    1],
                    [$getSkillId('Git'),    0],
                ],
            ],
            [
                'company_id'   => $companyIds[0],
                'title'        => 'Full Stack Web Intern',
                'description'  => 'Join our product team and build full stack features using React on the frontend and Node.js on the backend.',
                'location'     => 'Remote',
                'type'         => 'Remote',
                'duration'     => 6,
                'stipend'      => 12000,
                'vacancies'    => 3,
                'deadline'     => date('Y-m-d', strtotime('+45 days')),
                'status'       => 'Open',
                'skills'       => [
                    [$getSkillId('JavaScript'), 1],
                    [$getSkillId('React'),      1],
                    [$getSkillId('HTML/CSS'),   0],
                    [$getSkillId('Node.js'),    0],
                ],
            ],
            // DataWorks internships
            [
                'company_id'   => $companyIds[1],
                'title'        => 'Data Science Intern',
                'description'  => 'Work alongside our data science team on real-world ML projects. Analyze large datasets and build predictive models.',
                'location'     => 'Chittagong, Bangladesh',
                'type'         => 'Hybrid',
                'duration'     => 4,
                'stipend'      => 18000,
                'vacancies'    => 2,
                'deadline'     => date('Y-m-d', strtotime('+90 days')),
                'status'       => 'Open',
                'skills'       => [
                    [$getSkillId('Python'),           1],
                    [$getSkillId('Machine Learning'), 1],
                    [$getSkillId('Data Analysis'),    1],
                    [$getSkillId('SQL'),              0],
                ],
            ],
            [
                'company_id'   => $companyIds[1],
                'title'        => 'Business Intelligence Intern',
                'description'  => 'Help build dashboards and reports using Tableau and Excel to support business decision-making.',
                'location'     => 'Chittagong, Bangladesh',
                'type'         => 'On-site',
                'duration'     => 3,
                'stipend'      => 10000,
                'vacancies'    => 1,
                'deadline'     => date('Y-m-d', strtotime('+30 days')),
                'status'       => 'Open',
                'skills'       => [
                    [$getSkillId('Excel'),         1],
                    [$getSkillId('Tableau'),       1],
                    [$getSkillId('Data Analysis'), 0],
                ],
            ],
        ];

        $internshipIds = [];
        foreach ($internships as $i) {
            DB::insert(
                "INSERT INTO INTERNSHIPS
                    (COMPANY_ID, TITLE, DESCRIPTION, LOCATION, INTERNSHIP_TYPE,
                     DURATION_MONTHS, STIPEND, VACANCIES, APPLICATION_DEADLINE, STATUS)
                 VALUES
                    (:company_id, :title, :description, :location, :internship_type,
                     :duration_months, :stipend, :vacancies, :deadline, :status)",
                [
                    'company_id'      => $i['company_id'],
                    'title'           => $i['title'],
                    'description'     => $i['description'],
                    'location'        => $i['location'],
                    'internship_type' => $i['type'],
                    'duration_months' => $i['duration'],
                    'stipend'         => $i['stipend'],
                    'vacancies'       => $i['vacancies'],
                    'deadline'        => $i['deadline'],
                    'status'          => $i['status'],
                ]
            );

            // Get ID of just-inserted internship
            $row = DB::select(
                "SELECT INTERNSHIP_ID FROM INTERNSHIPS
                 WHERE COMPANY_ID = :company_id
                 ORDER BY CREATED_AT DESC FETCH FIRST 1 ROWS ONLY",
                ['company_id' => $i['company_id']]
            );

            // Oracle 11g fallback if FETCH FIRST fails
            if (empty($row)) {
                $row = DB::select(
                    "SELECT * FROM (
                        SELECT INTERNSHIP_ID FROM INTERNSHIPS
                        WHERE COMPANY_ID = :company_id
                        ORDER BY CREATED_AT DESC
                    ) WHERE ROWNUM = 1",
                    ['company_id' => $i['company_id']]
                );
            }

            $internshipId = (int) $row[0]->internship_id;
            $internshipIds[] = $internshipId;

            // Attach skills
            foreach ($i['skills'] as [$skillId, $isMandatory]) {
                if ($skillId === 0) continue;
                DB::insert(
                    "INSERT INTO INTERNSHIP_SKILLS (INTERNSHIP_ID, SKILL_ID, IS_MANDATORY)
                     VALUES (:internship_id, :skill_id, :is_mandatory)",
                    [
                        'internship_id' => $internshipId,
                        'skill_id'      => $skillId,
                        'is_mandatory'  => $isMandatory,
                    ]
                );
            }
        }

        $this->command->info('Internships seeded.');

        // ── APPLICATIONS ───────────────────────────────────────────────
        // Sakib applies to Backend Developer and Data Science
        // Priya applies to Data Science (Accepted) and BI Intern (Shortlisted)
        // Rafiq applies to Full Stack (Interview)
        $applications = [
            [$studentIds[0], $internshipIds[0], 'Reviewed',    'I have strong Laravel and SQL skills and am excited to contribute to backend systems.'],
            [$studentIds[0], $internshipIds[2], 'Pending',     'I am learning Python and ML and would love to work on real-world data problems.'],
            [$studentIds[1], $internshipIds[2], 'Accepted',    'My background in data analysis and machine learning aligns perfectly with this role.'],
            [$studentIds[1], $internshipIds[3], 'Shortlisted', 'I have hands-on experience with Excel and Tableau from university projects.'],
            [$studentIds[2], $internshipIds[1], 'Interview',   'As a React and JavaScript developer, I am ready to contribute to your product team.'],
        ];

        $applicationIds = [];
        foreach ($applications as [$studentId, $internshipId, $status, $coverLetter]) {
            DB::insert(
                "INSERT INTO APPLICATIONS
                    (INTERNSHIP_ID, STUDENT_ID, COVER_LETTER, STATUS)
                 VALUES
                    (:internship_id, :student_id, :cover_letter, :status)",
                [
                    'internship_id' => $internshipId,
                    'student_id'    => $studentId,
                    'cover_letter'  => $coverLetter,
                    'status'        => $status,
                ]
            );

            $row = DB::select(
                "SELECT * FROM (
                    SELECT APPLICATION_ID FROM APPLICATIONS
                    WHERE STUDENT_ID = :student_id
                    AND INTERNSHIP_ID = :internship_id
                    ORDER BY APPLIED_AT DESC
                ) WHERE ROWNUM = 1",
                ['student_id' => $studentId, 'internship_id' => $internshipId]
            );
            $applicationIds[] = (int) $row[0]->application_id;
        }

        $this->command->info('Applications seeded.');

        // ── INTERVIEW ──────────────────────────────────────────────────
        // Rafiq has an interview scheduled (applicationIds[4] = his Full Stack app)
        DB::insert(
            "INSERT INTO INTERVIEWS
                (APPLICATION_ID, SCHEDULED_DATE, SCHEDULED_TIME,
                 INTERVIEW_MODE, LOCATION_OR_LINK, NOTES)
             VALUES
                (:application_id, :scheduled_date, :scheduled_time,
                 :interview_mode, :location_or_link, :notes)",
            [
                'application_id'   => $applicationIds[4],
                'scheduled_date'   => date('Y-m-d', strtotime('+7 days')),
                'scheduled_time'   => '10:30',
                'interview_mode'   => 'Video',
                'location_or_link' => 'https://meet.google.com/demo-link',
                'notes'            => 'Please prepare a 5-minute intro about your React projects.',
            ]
        );

        $this->command->info('Interview seeded.');

        // ── NOTIFICATIONS ──────────────────────────────────────────────
        // Notify Priya she was accepted
        DB::insert(
            "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE)
             VALUES (:user_id, :message)",
            [
                'user_id' => $student_users[1],
                'message' => 'Congratulations! Your application for "Data Science Intern" has been accepted!',
            ]
        );

        // Notify Rafiq about his interview
        DB::insert(
            "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE)
             VALUES (:user_id, :message)",
            [
                'user_id' => $student_users[2],
                'message' => 'Your interview for "Full Stack Web Intern" has been scheduled on '
                    . date('d M Y', strtotime('+7 days')) . ' at 10:30 AM.',
            ]
        );

        // Notify Sakib his application was reviewed
        DB::insert(
            "INSERT INTO NOTIFICATIONS (USER_ID, MESSAGE)
             VALUES (:user_id, :message)",
            [
                'user_id' => $student_users[0],
                'message' => 'Your application for "Backend Developer Intern" status changed from Pending to Reviewed.',
            ]
        );

        $this->command->info('Notifications seeded.');

        // ── AUDIT LOG ─────────────────────────────────────────────────
        DB::insert(
            "INSERT INTO AUDIT_LOG (USER_ID, ACTION, TABLE_NAME, RECORD_ID, OLD_VALUE, NEW_VALUE)
             VALUES (:user_id, :action, :table_name, :record_id, :old_value, :new_value)",
            [
                'user_id'    => 1,
                'action'     => 'UPDATE_STATUS',
                'table_name' => 'APPLICATIONS',
                'record_id'  => $applicationIds[0],
                'old_value'  => 'Pending',
                'new_value'  => 'Reviewed',
            ]
        );

        DB::insert(
            "INSERT INTO AUDIT_LOG (USER_ID, ACTION, TABLE_NAME, RECORD_ID, OLD_VALUE, NEW_VALUE)
             VALUES (:user_id, :action, :table_name, :record_id, :old_value, :new_value)",
            [
                'user_id'    => 1,
                'action'     => 'UPDATE_STATUS',
                'table_name' => 'APPLICATIONS',
                'record_id'  => $applicationIds[2],
                'old_value'  => 'Pending',
                'new_value'  => 'Accepted',
            ]
        );

        $this->command->info('Audit log seeded.');

        $this->command->info('');
        $this->command->info('✅ Demo seeding complete!');
        $this->command->info('');
        $this->command->info('LOGIN CREDENTIALS:');
        $this->command->info('Admin:   admin@careerbridge.com  / Admin@12345');
        $this->command->info('Company: hr@techcorp.com         / Password123');
        $this->command->info('Company: hr@dataworks.com        / Password123');
        $this->command->info('Student: sakib@student.com       / Password123');
        $this->command->info('Student: priya@student.com       / Password123');
        $this->command->info('Student: rafiq@student.com       / Password123');
    }
}