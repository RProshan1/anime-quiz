<?php
session_start();
require_once 'data.php';

if (!isset($_SESSION['quiz_done'])) {
    header('Location: index.php');
    exit;
}
$q     = $_SESSION['quiz_done'];
$score = $q['score'];
$total = count($q['questions']);
$pct   = round(($score / max($total,1)) * 100);
$time  = time() - $q['start'];
$lb    = get_leaderboard();
$rank  = 1;
foreach ($lb as $entry) {
    if ($entry['name'] === $q['player'] && $entry['score'] === $score) break;
    $rank++;
}

$grade = match(true) {
    $pct >= 90 => ['S', 'You are the chosen one! Pure anime mastery.', 'badge-gold'],
    $pct >= 75 => ['A', 'Impressive! Worthy of the Survey Corps.', 'badge-success'],
    $pct >= 60 => ['B', 'Solid otaku knowledge. Keep watching!', 'badge-cyan'],
    $pct >= 40 => ['C', 'Not bad, but there\'s more to learn.', 'badge-accent'],
    default    => ['D', 'Time to binge more anime, friend.', 'badge-danger'],
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Result – AniQuiz</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .result-hero { text-align:center; padding:3rem 1.5rem 2rem; }
    .grade-ring {
      width:140px; height:140px; border-radius:50%;
      border:4px solid var(--accent);
      display:flex; align-items:center; justify-content:center;
      margin:0 auto 1.5rem;
      background:radial-gradient(circle, rgba(224,64,251,.12), transparent);
      box-shadow: 0 0 40px rgba(224,64,251,.3);
      animation: pop .5s cubic-bezier(.175,.885,.32,1.275);
    }
    @keyframes pop { from { transform:scale(0); opacity:0; } to { transform:scale(1); opacity:1; } }
    .grade-letter { font-family:'Bangers',cursive; font-size:5rem; line-height:1;
      background:linear-gradient(135deg,var(--accent),var(--accent2));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    .score-big { font-family:'Bangers',cursive; font-size:4rem; letter-spacing:2px; line-height:1; }
    .score-label { color:var(--muted); font-size:.9rem; margin-bottom:1rem; }
    .meta-row { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin:1.5rem 0; }
    .meta-pill {
      background:var(--card); border:1px solid var(--border);
      border-radius:50px; padding:.5rem 1.3rem;
      font-size:.85rem; font-weight:700; color:var(--muted);
    }
    .meta-pill strong { color:var(--text); }

    /* Review */
    .review-item {
      background:var(--card); border:1px solid var(--border);
      border-radius:12px; padding:1.2rem 1.4rem; margin-bottom:.85rem;
    }
    .review-item.correct { border-left:4px solid var(--success); }
    .review-item.wrong   { border-left:4px solid var(--danger); }
    .review-q { font-weight:700; margin-bottom:.6rem; font-size:.95rem; }
    .review-opts { display:flex; flex-direction:column; gap:.3rem; }
    .review-opt { font-size:.85rem; display:flex; align-items:center; gap:.5rem; color:var(--muted); }
    .review-opt.chosen-right { color:var(--success); font-weight:700; }
    .review-opt.chosen-wrong { color:var(--danger);  font-weight:700; }
    .review-opt.correct-ans  { color:var(--success); font-weight:700; }
    .btn-row { display:flex; gap:1rem; justify-content:center; flex-wrap:wrap; margin:2rem 0; }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-brand" href="index.php">⚡ AniQuiz</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="quiz.php">Play</a>
    <a href="leaderboard.php">Leaderboard</a>
    <a href="admin/index.php">Admin</a>
  </div>
</nav>

<div class="result-hero">
  <div class="grade-ring">
    <span class="grade-letter"><?= $grade[0] ?></span>
  </div>
  <div class="score-big glow"><?= $score ?><span style="font-size:2rem;color:var(--muted);">/<?= $total ?></span></div>
  <div class="score-label"><?= $pct ?>% Correct</div>
  <p style="color:var(--muted);max-width:420px;margin:0 auto .5rem;"><?= $grade[1] ?></p>
  <span class="badge <?= $grade[2] ?>" style="font-size:.9rem;margin-top:.5rem;">Rank <?= $rank ?> on Leaderboard</span>

  <div class="meta-row">
    <div class="meta-pill">👤 <strong><?= htmlspecialchars($q['player']) ?></strong></div>
    <div class="meta-pill">⏱ <strong><?= $time ?>s</strong> total time</div>
    <div class="meta-pill">🎯 <strong><?= $score ?></strong> correct</div>
    <div class="meta-pill">💀 <strong><?= $total - $score ?></strong> wrong</div>
  </div>

  <div class="btn-row">
    <a href="quiz.php" class="btn btn-primary">🔄 Play Again</a>
    <a href="leaderboard.php" class="btn btn-cyan">🏆 Leaderboard</a>
    <a href="index.php" class="btn btn-ghost">🏠 Home</a>
  </div>
</div>

<div class="container" style="padding-top:0">
  <h3 style="font-family:'Bangers',cursive;letter-spacing:1px;font-size:1.8rem;margin-bottom:1.2rem;">
    📋 Answer Review
  </h3>

  <?php foreach ($q['questions'] as $i => $qdata):
    $ans     = $q['answers'][$i] ?? ['selected' => -1, 'correct' => $qdata['answer'], 'right' => false];
    $letters = ['A','B','C','D'];
  ?>
  <div class="review-item <?= $ans['right'] ? 'correct' : 'wrong' ?>">
    <div class="review-q">
      <?= $ans['right'] ? '✅' : '❌' ?> Q<?= $i+1 ?>. <?= htmlspecialchars($qdata['question']) ?>
    </div>
    <div class="review-opts">
      <?php foreach ($qdata['options'] as $oi => $opt):
        $isChosen  = ($ans['selected'] === $oi);
        $isCorrect = ($ans['correct']  === $oi);
        $cls = '';
        if ($isChosen && $ans['right'])  $cls = 'chosen-right';
        elseif ($isChosen && !$ans['right']) $cls = 'chosen-wrong';
        elseif ($isCorrect && !$ans['right']) $cls = 'correct-ans';
      ?>
      <div class="review-opt <?= $cls ?>">
        <span><?= $letters[$oi] ?>.</span>
        <?= htmlspecialchars($opt) ?>
        <?php if ($isChosen && $ans['right']): ?> ✔<?php endif; ?>
        <?php if ($isChosen && !$ans['right']): ?> ✗ (your answer)<?php endif; ?>
        <?php if ($isCorrect && !$ans['right']): ?> ← correct<?php endif; ?>
      </div>
      <?php endforeach; ?>
      <?php if ($ans['selected'] === -1): ?>
        <div class="review-opt chosen-wrong">⏰ Time ran out – no answer selected</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

</body>
</html>
