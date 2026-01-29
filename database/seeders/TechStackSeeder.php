<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProgrammingLanguage;
use App\Models\Framework;
use App\Models\Database;
use App\Models\ServerLocation;

class TechStackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Programming Languages
        $php = ProgrammingLanguage::firstOrCreate(['name' => 'PHP']);
        $python = ProgrammingLanguage::firstOrCreate(['name' => 'Python']);
        $javascript = ProgrammingLanguage::firstOrCreate(['name' => 'JavaScript']);
        $java = ProgrammingLanguage::firstOrCreate(['name' => 'Java']);
        $csharp = ProgrammingLanguage::firstOrCreate(['name' => 'C#']);
        $go = ProgrammingLanguage::firstOrCreate(['name' => 'Go']);
        $ruby = ProgrammingLanguage::firstOrCreate(['name' => 'Ruby']);
        $typescript = ProgrammingLanguage::firstOrCreate(['name' => 'TypeScript']);

        // Frameworks for PHP
        Framework::firstOrCreate([
            'name' => 'Laravel',
            'programming_language_id' => $php->id
        ]);
        Framework::firstOrCreate([
            'name' => 'CodeIgniter',
            'programming_language_id' => $php->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Symfony',
            'programming_language_id' => $php->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Yii',
            'programming_language_id' => $php->id
        ]);

        // Frameworks for Python
        Framework::firstOrCreate([
            'name' => 'Django',
            'programming_language_id' => $python->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Flask',
            'programming_language_id' => $python->id
        ]);
        Framework::firstOrCreate([
            'name' => 'FastAPI',
            'programming_language_id' => $python->id
        ]);

        // Frameworks for JavaScript
        Framework::firstOrCreate([
            'name' => 'Express.js',
            'programming_language_id' => $javascript->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Next.js',
            'programming_language_id' => $javascript->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Nest.js',
            'programming_language_id' => $javascript->id
        ]);

        // Frameworks for TypeScript
        Framework::firstOrCreate([
            'name' => 'Angular',
            'programming_language_id' => $typescript->id
        ]);
        Framework::firstOrCreate([
            'name' => 'Next.js',
            'programming_language_id' => $typescript->id
        ]);

        // Frameworks for Java
        Framework::firstOrCreate([
            'name' => 'Spring Boot',
            'programming_language_id' => $java->id
        ]);

        // Frameworks for C#
        Framework::firstOrCreate([
            'name' => 'ASP.NET Core',
            'programming_language_id' => $csharp->id
        ]);

        // Frameworks for Ruby
        Framework::firstOrCreate([
            'name' => 'Ruby on Rails',
            'programming_language_id' => $ruby->id
        ]);

        // Databases
        Database::firstOrCreate(['name' => 'MySQL']);
        Database::firstOrCreate(['name' => 'PostgreSQL']);
        Database::firstOrCreate(['name' => 'SQL Server']);
        Database::firstOrCreate(['name' => 'Oracle']);
        Database::firstOrCreate(['name' => 'MongoDB']);
        Database::firstOrCreate(['name' => 'MariaDB']);
        Database::firstOrCreate(['name' => 'SQLite']);
        Database::firstOrCreate(['name' => 'Redis']);
        Database::firstOrCreate(['name' => 'Firebase']);

        // Server Locations
        ServerLocation::firstOrCreate(['name' => 'Bulungan']);
        ServerLocation::firstOrCreate(['name' => 'Tarakan']);
        ServerLocation::firstOrCreate(['name' => 'Malinau']);
        ServerLocation::firstOrCreate(['name' => 'Nunukan']);
        ServerLocation::firstOrCreate(['name' => 'Jakarta']);
        ServerLocation::firstOrCreate(['name' => 'Surabaya']);
        ServerLocation::firstOrCreate(['name' => 'Balikpapan']);
        ServerLocation::firstOrCreate(['name' => 'Samarinda']);
        ServerLocation::firstOrCreate(['name' => 'Cloud Provider (AWS, GCP, Azure, dll)']);

        $this->command->info('Tech stack master data seeded successfully!');
    }
}
