<?php
// ─── Anime Quiz Data Store ───────────────────────────────────────────────────
// All quiz data lives here as PHP arrays. Admin panel edits questions.json;
// this file reads it and falls back to defaults if not found.

$questions_file = __DIR__ . '/../data/questions.json';

function get_questions(): array {
    global $questions_file;
    if (file_exists($questions_file)) {
        $data = json_decode(file_get_contents($questions_file), true);
        if (is_array($data) && count($data) > 0) return $data;
    }
    return get_default_questions();
}

function save_questions(array $questions): bool {
    global $questions_file;
    $dir = dirname($questions_file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    return file_put_contents($questions_file, json_encode($questions, JSON_PRETTY_PRINT)) !== false;
}

function get_leaderboard(): array {
    $file = __DIR__ . '/../data/leaderboard.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            usort($data, fn($a, $b) => $b['score'] <=> $a['score']);
            return array_slice($data, 0, 10);
        }
    }
    return [];
}

function save_score(string $name, int $score, int $total, int $time_taken): void {
    $file = __DIR__ . '/../data/leaderboard.json';
    $dir  = dirname($file);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $data = [];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?? [];
    }
    $data[] = [
        'name'       => htmlspecialchars($name),
        'score'      => $score,
        'total'      => $total,
        'percentage' => round(($score / max($total, 1)) * 100),
        'time'       => $time_taken,
        'date'       => date('Y-m-d H:i'),
    ];
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_default_questions(): array {
    return [
        [
            'id'       => 1,
            'question' => 'Which anime features a boy named Naruto who dreams of becoming Hokage?',
            'options'  => ['Bleach', 'Naruto', 'One Piece', 'Dragon Ball Z'],
            'answer'   => 1,
            'category' => 'Shonen',
        ],
        [
            'id'       => 2,
            'question' => 'In "Attack on Titan," what are the giant humanoid creatures called?',
            'options'  => ['Colossi', 'Titans', 'Giants', 'Shifters'],
            'answer'   => 1,
            'category' => 'Action',
        ],
        [
            'id'       => 3,
            'question' => 'Which studio produced "Spirited Away"?',
            'options'  => ['Toei Animation', 'Madhouse', 'Studio Ghibli', 'Kyoto Animation'],
            'answer'   => 2,
            'category' => 'Movies',
        ],
        [
            'id'       => 4,
            'question' => 'What is the name of the death god notebook in "Death Note"?',
            'options'  => ['Shinigami Book', 'Death Note', 'Kira\'s Journal', 'Soul Ledger'],
            'answer'   => 1,
            'category' => 'Thriller',
        ],
        [
            'id'       => 5,
            'question' => 'In "One Piece," what is the name of Luffy\'s pirate crew?',
            'options'  => ['Red Hair Pirates', 'Straw Hat Pirates', 'Whitebeard Pirates', 'Black Beard Pirates'],
            'answer'   => 1,
            'category' => 'Shonen',
        ],
        [
            'id'       => 6,
            'question' => 'Which anime follows a pianist named Kousei Arima?',
            'options'  => ['Your Lie in April', 'Clannad', 'Anohana', 'Toradora'],
            'answer'   => 0,
            'category' => 'Romance',
        ],
        [
            'id'       => 7,
            'question' => 'What power does Tanjiro Kamado wield in "Demon Slayer"?',
            'options'  => ['Water & Sun Breathing', 'Fire & Thunder Breathing', 'Wind & Moon Breathing', 'Ice & Earth Breathing'],
            'answer'   => 0,
            'category' => 'Action',
        ],
        [
            'id'       => 8,
            'question' => 'In "Fullmetal Alchemist: Brotherhood," what is the law of equivalent exchange?',
            'options'  => ['You must pay with gold', 'To gain something, you must give something of equal value', 'Only the Philosopher\'s Stone breaks the law', 'Blood is the only true currency'],
            'answer'   => 1,
            'category' => 'Adventure',
        ],
        [
            'id'       => 9,
            'question' => 'What is the highest rank a hero can achieve in "My Hero Academia"?',
            'options'  => ['S-Class Hero', 'Symbol of Peace', 'Number 1 Pro Hero', 'Grand Hero'],
            'answer'   => 2,
            'category' => 'Shonen',
        ],
        [
            'id'       => 10,
            'question' => 'Which anime is set in a virtual reality MMORPG called "Aincrad"?',
            'options'  => ['Log Horizon', '.hack//Sign', 'Sword Art Online', 'Overlord'],
            'answer'   => 2,
            'category' => 'Sci-Fi',
        ],
    ];
}
