<?php
require_once 'data.php';
$questions = get_questions();
$total_q   = count($questions);
$lb        = get_leaderboard();
$top_score = $lb[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AniQuiz – Ultimate Anime Challenge</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .hero {
      text-align: center;
      padding: 5rem 1.5rem 3rem;
      position: relative;
    }
    .hero-eyebrow {
      font-size: .8rem; letter-spacing: 4px; text-transform: uppercase;
      color: var(--accent2); font-weight: 700; margin-bottom: 1rem;
    }
    .hero-title {
      font-family: 'Bangers', cursive;
      font-size: clamp(3.5rem, 10vw, 7rem);
      line-height: 1;
      background: linear-gradient(135deg, #fff 30%, var(--accent), var(--accent2));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 1.2rem;
    }
    .hero-sub {
      color: var(--muted); max-width: 480px; margin: 0 auto 2.5rem;
      font-size: 1.1rem; line-height: 1.7;
    }
    .stats-row {
      display: flex; justify-content: center; gap: 2rem;
      margin: 3rem 0; flex-wrap: wrap;
    }
    .stat-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.5rem 2.5rem;
      text-align: center;
      box-shadow: var(--shadow);
    }
    .stat-num {
      font-family: 'Bangers', cursive;
      font-size: 3rem; letter-spacing: 2px;
      background: linear-gradient(135deg, var(--accent), var(--accent2));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .stat-label { color: var(--muted); font-size: .8rem; text-transform: uppercase; letter-spacing: 1px; margin-top: .3rem; }
    .top-bar {
      background: linear-gradient(135deg, rgba(224,64,251,.1), rgba(0,229,255,.08));
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 1.2rem 2rem;
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 2rem;
    }
    .top-bar .crown { font-size: 1.6rem; }
    .top-bar .leader-info { flex: 1; }
    .top-bar .leader-name { font-weight: 700; font-size: 1.1rem; }
    .top-bar .leader-score { color: var(--muted); font-size: .85rem; }
    .btn-group { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
    .floating-sakura {
      position: fixed; pointer-events: none; z-index: -1;
      top: 0; left: 0; width: 100%; height: 100%;
      overflow: hidden;
    }
    .petal {
      position: absolute;
      width: 8px; height: 8px;
      border-radius: 50% 0 50% 0;
      background: rgba(224,64,251,.15);
      animation: fall linear infinite;
    }
    @keyframes fall {
      0%   { transform: translateY(-20px) rotate(0deg); opacity: .8; }
      100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
    }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-brand" href="index.php">⚡ AniQuiz</a>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="quiz.php">Play</a>
    <a href="leaderboard.php">Leaderboard</a>
    <a href="admin/index.php">Admin</a>
  </div>
</nav>

<div class="floating-sakura" id="sakura"></div>

<div class="hero">
  <p class="hero-eyebrow">⛩ Test Your Anime Knowledge</p>
  <h1 class="hero-title">AniQuiz</h1>
  <p class="hero-sub">
    <?= $total_q ?> hand-crafted questions across action, thriller, adventure &amp; more.
    Beat the clock. Top the leaderboard. Prove you're the ultimate otaku.
  </p>
  <div class="btn-group">
    <a href="quiz.php" class="btn btn-primary">▶ Start Quiz</a>
    <a href="leaderboard.php" class="btn btn-ghost">🏆 Leaderboard</a>
  </div>
</div>

<div class="container">
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-num"><?= $total_q ?></div>
      <div class="stat-label">Questions</div>
    </div>
    <div class="stat-card">
      <div class="stat-num">15s</div>
      <div class="stat-label">Per Question</div>
    </div>
    <div class="stat-card">
      <div class="stat-num"><?= count($lb) ?></div>
      <div class="stat-label">Players</div>
    </div>
  </div>

  <?php if ($top_score): ?>
  <div class="top-bar">
    <div class="crown">👑</div>
    <div class="leader-info">
      <div class="leader-name"><?= htmlspecialchars($top_score['name']) ?></div>
      <div class="leader-score">Current #1 Champion</div>
    </div>
    <div>
      <span class="badge badge-gold"><?= $top_score['percent'] ?>% • <?= $top_score['score'] ?>/<?= $top_score['total'] ?></span>
    </div>
  </div>
  <?php endif; ?>

  <div style="text-align:center; margin-top:2rem;">
    <a href="quiz.php" class="btn btn-cyan" style="font-size:1.2rem; padding:1rem 3rem;">
      ⚡ Challenge Accepted
    </a>
  </div>
</div>

<script>
// Floating petals
const sakura = document.getElementById('sakura');
for (let i = 0; i < 18; i++) {
  const p = document.createElement('div');
  p.className = 'petal';
  p.style.left   = Math.random() * 100 + 'vw';
  p.style.animationDuration = (6 + Math.random() * 10) + 's';
  p.style.animationDelay   = (Math.random() * 8) + 's';
  p.style.width = p.style.height = (5 + Math.random() * 8) + 'px';
  p.style.background = Math.random() > .5
    ? 'rgba(224,64,251,.12)' : 'rgba(0,229,255,.1)';
  sakura.appendChild(p);
}
</script>
</body>
</html>
