<?php

namespace App\Utils;

class SchemaDefinition
{
    public static function getExpectedSchema()
    {
        return [
            'users' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(20) DEFAULT 'admin',
                    prodi_id VARCHAR(50) NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'semesters' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS semesters (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    kode VARCHAR(10) UNIQUE NOT NULL,
                    nama VARCHAR(255) NOT NULL,
                    periode INTEGER DEFAULT 0,
                    is_active BOOLEAN DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'surveys' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS surveys (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(255) NOT NULL,
                    target_role VARCHAR(50) NOT NULL,
                    is_active BOOLEAN DEFAULT 1,
                    description TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )"
            ],
            'survey_questions' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_questions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    survey_id INTEGER NOT NULL,
                    code VARCHAR(20) NULL,
                    question_text TEXT NOT NULL,
                    category VARCHAR(100) NULL,
                    order_num INTEGER DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
                )"
            ],
            'survey_responses' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_responses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    survey_id INTEGER NOT NULL,
                    user_id INTEGER NULL,
                    respondent_identifier VARCHAR(100) NULL,
                    respondent_type VARCHAR(50) NOT NULL,
                    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    suggestion TEXT,
                    FOREIGN KEY(survey_id) REFERENCES surveys(id) ON DELETE CASCADE
                )"
            ],
            'survey_answers' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS survey_answers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    response_id INTEGER NOT NULL,
                    question_id INTEGER NOT NULL,
                    score INTEGER NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
                    FOREIGN KEY(question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
                )"
            ],
            // Add other tables as needed (news, guides, etc.)
            'update_logs' => [
                'create_sql' => "CREATE TABLE IF NOT EXISTS update_logs (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    version_from VARCHAR(20),
                    version_to VARCHAR(20),
                    status VARCHAR(20),
                    message TEXT,
                    performed_by INTEGER,
                    backup_file VARCHAR(255),
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY(performed_by) REFERENCES users(id)
                )"
            ]
        ];
    }
}
