<?php
// ─────────────────────────────────────────────
//  ANIME QUIZ  –  Data Store (PHP Arrays)
// ─────────────────────────────────────────────

// Leaderboard stored in a flat JSON file
define('LEADERBOARD_FILE', __DIR__ . '/leaderboard.json');

function get_questions(): array {
    $file = __DIR__ . '/questions.json';
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data) && count($data)) return $data;
    }
    // Default questions
    return [
        [
            'id'      => 1,
            'question'=> 'Which anime features the "Survey Corps" fighting Titans?',
            'options' => ['Demon Slayer', 'Attack on Titan', 'Naruto', 'Bleach'],
            'answer'  => 1,
            'category'=> 'Action',
        ],
        [
            'id'      => 2,
            'question'=> 'What is the name of the main character in "Death Note"?',
            'options' => ['L Lawliet', 'Near', 'Light Yagami', 'Mello'],
            'answer'  => 2,
            'category'=> 'Thriller',
        ],
        [
            'id'      => 3,
            'question'=> 'In "Fullmetal Alchemist: Brotherhood", what did Edward Elric lose in his left leg?',
            'options' => ['His soul', 'His arm', 'His leg', 'His sight'],
            'answer'  => 2,
            'category'=> 'Adventure',
        ],
        [
            'id'      => 4,
            'question'=> 'Who is the pirate king in "One Piece"?',
            'options' => ['Shanks', 'Whitebeard', 'Gol D. Roger', 'Monkey D. Luffy'],
            'answer'  => 2,
            'category'=> 'Adventure',
        ],
        [
            'id'      => 5,
            'question'=> 'What is the hidden village of Naruto Uzumaki?',
            'options' => ['Village Hidden in Mist', 'Village Hidden in Sand', 'Village Hidden in Leaves', 'Village Hidden in Clouds'],
            'answer'  => 2,
            'category'=> 'Action',
        ],
        [
            'id'      => 6,
            'question'=> 'Which studio produced "Spirited Away"?',
            'options' => ['Toei Animation', 'Madhouse', 'Studio Ghibli', 'Gainax'],
            'answer'  => 2,
            'category'=> 'Film',
        ],
        [
            'id'      => 7,
            'question'=> 'In "Dragon Ball Z", what is the highest power level Vegeta declares before fighting?',
            'options' => ['8000', '9000', '10000', '7000'],
            'answer'  => 1,
            'category'=> 'Action',
        ],
        [
            'id'      => 8,
            'question'=> 'What Quirk does Izuku Midoriya inherit in "My Hero Academia"?',
            'options' => ['All For One', 'One For All', 'Half-Cold Half-Hot', 'Explosion'],
            'answer'  => 1,
            'category'=> 'Action',
        ],
        [
            'id'      => 9,
            'question'=> 'In "Sword Art Online", what is the name of the first VRMMORPG?',
            'options' => ['ALfheim Online', 'Gun Gale Online', 'Sword Art Online', 'Ordinal Scale'],
            'answer'  => 2,
            'category'=> 'Sci-Fi',
        ],
        [
            'id'      => 10,
            'question'=> 'Who created the anime "Cowboy Bebop"?',
            'options' => ['Hayao Miyazaki', 'Satoshi Kon', 'Shinichiro Watanabe', 'Isao Takahata'],
            'answer'  => 2,
            'category'=> 'Sci-Fi',
        ],
    ];
}

function save_questions(array $questions): void {
    file_put_contents(__DIR__ . '/questions.json', json_encode(array_values($questions), JSON_PRETTY_PRINT));
}

function get_leaderboard(): array {
    if (!file_exists(LEADERBOARD_FILE)) return [];
    $data = json_decode(file_get_contents(LEADERBOARD_FILE), true);
    return is_array($data) ? $data : [];
}

function save_score(string $name, int $score, int $total, int $time_taken): void {
    $lb = get_leaderboard();
    $lb[] = [
        'name'       => htmlspecialchars(trim($name)),
        'score'      => $score,
        'total'      => $total,
        'percent'    => round(($score / max($total, 1)) * 100),
        'time'       => $time_taken,
        'date'       => date('Y-m-d H:i'),
    ];
    usort($lb, fn($a, $b) => $b['percent'] <=> $a['percent'] ?: $a['time'] <=> $b['time']);
    $lb = array_slice($lb, 0, 50); // keep top 50
    file_put_contents(LEADERBOARD_FILE, json_encode($lb, JSON_PRETTY_PRINT));
}
