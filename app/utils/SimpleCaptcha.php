<?php

namespace App\Utils;

class SimpleCaptcha
{
    /**
     * Generate a simple math captcha
     * Returns ['question' => 'What is 5 + 3?', 'answer' => 8]
     */
    public static function generate()
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];

        switch ($operator) {
            case '+':
                $question = "Berapa hasil dari $num1 + $num2?";
                $answer = $num1 + $num2;
                break;
            case '-':
                // Make sure result is positive
                if ($num1 < $num2) {
                    $temp = $num1;
                    $num1 = $num2;
                    $num2 = $temp;
                }
                $question = "Berapa hasil dari $num1 - $num2?";
                $answer = $num1 - $num2;
                break;
        }

        // Store answer in session
        $_SESSION['captcha_answer'] = $answer;

        return [
            'question' => $question,
            'answer' => $answer
        ];
    }

    /**
     * Verify captcha answer
     */
    public static function verify($userAnswer)
    {
        if (!isset($_SESSION['captcha_answer'])) {
            return false;
        }

        $correctAnswer = $_SESSION['captcha_answer'];
        
        // Clear captcha after verification
        unset($_SESSION['captcha_answer']);

        return (int)$userAnswer === (int)$correctAnswer;
    }

    /**
     * Get current captcha question (if exists in session)
     */
    public static function getQuestion()
    {
        if (!isset($_SESSION['captcha_answer'])) {
            return self::generate();
        }
        
        // Reconstruct question from stored answer (simplified approach)
        // In production, you might want to store the question too
        return self::generate();
    }
}
