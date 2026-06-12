<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // Programming
            ['SKILL_NAME' => 'Python',           'CATEGORY' => 'Programming'],
            ['SKILL_NAME' => 'Java',              'CATEGORY' => 'Programming'],
            ['SKILL_NAME' => 'PHP',               'CATEGORY' => 'Programming'],
            ['SKILL_NAME' => 'JavaScript',        'CATEGORY' => 'Programming'],
            ['SKILL_NAME' => 'C++',               'CATEGORY' => 'Programming'],
            ['SKILL_NAME' => 'SQL',               'CATEGORY' => 'Programming'],
            // Web
            ['SKILL_NAME' => 'Laravel',           'CATEGORY' => 'Web Development'],
            ['SKILL_NAME' => 'React',             'CATEGORY' => 'Web Development'],
            ['SKILL_NAME' => 'HTML/CSS',          'CATEGORY' => 'Web Development'],
            ['SKILL_NAME' => 'Node.js',           'CATEGORY' => 'Web Development'],
            // Data
            ['SKILL_NAME' => 'Machine Learning',  'CATEGORY' => 'Data Science'],
            ['SKILL_NAME' => 'Data Analysis',     'CATEGORY' => 'Data Science'],
            ['SKILL_NAME' => 'Excel',             'CATEGORY' => 'Data Science'],
            ['SKILL_NAME' => 'Tableau',           'CATEGORY' => 'Data Science'],
            // Tools
            ['SKILL_NAME' => 'Git',               'CATEGORY' => 'Tools'],
            ['SKILL_NAME' => 'Docker',            'CATEGORY' => 'Tools'],
            ['SKILL_NAME' => 'Linux',             'CATEGORY' => 'Tools'],
            // Soft Skills
            ['SKILL_NAME' => 'Communication',     'CATEGORY' => 'Soft Skills'],
            ['SKILL_NAME' => 'Teamwork',          'CATEGORY' => 'Soft Skills'],
            ['SKILL_NAME' => 'Problem Solving',   'CATEGORY' => 'Soft Skills'],
            ['SKILL_NAME' => 'Project Management','CATEGORY' => 'Soft Skills'],
        ];

        foreach ($skills as $skill) {
            DB::table('SKILLS')->insert($skill);
        }
    }
}