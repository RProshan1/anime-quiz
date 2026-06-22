<?php
require_once 'data.php';
$lb = get_leaderboard();

if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    file_put_contents(LEADERBOARD_FILE, '[]');
    header('Location: leaderboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leaderboard – AniQuiz</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .lb-header { text-align:center; padding:3rem 1.5rem 1rem; }
    .lb-title { font-family:'Bangers',cursive; font-size:4rem; letter-spacing:3px; }
    .podium { display:flex; align-items:flex-end; justify-content:center; gap:1.5rem; margin:2rem 0 3rem; flex-wrap:wrap; }
    .podium-card {
      text-align:center; border-radius:var(--radius);
      border:1px solid var(--border); padding:1.5rem 2rem;
      background:var(--card); min-width:140px; box-shadow:var(--shadow);
    }
    .podium-card.first  { border-color:var(--gold);    box-shadow:0 0 30px rgba(255,215,0,.25); order:-1; padding-top:2.2rem; }
    .podium-card.second { border-color:#aaa; }
    .podium-card.third  { border-color:#cd7f32; }
    .podium-medal { font-size:2.5rem; }
    .podium-name  { font-weight:700; font-size:1rem; margin:.3rem 0; }
    .podium-pct   { font-family:'Bangers',cursive; font-size:2.2rem; letter-spacing:1px; }
    .podium-card.first  .podium-pct { color:var(--gold); }
    .podium-card.second .podium-pct { color:#aaa; }
    .podium-card.third  .podium-pct { color:#cd7f32; }
    .rank-num { font-family:'Bangers',cursive; font-size:1.5rem; color:var(--muted); width:2.5ch; }
    .rank-badge-1 { color:var(--gold); }
    .rank-badge-2 { color:#aaa; }
    .rank-badge-3 { color:#cd7f32; }
    .empty-state { text-align:center; padding:4rem 2rem; color:var(--muted); }
    .empty-state .emoji { font-size:4rem; display:block; margin-bottom:1rem; }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-brand" href="index.php">⚡ AniQuiz</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="quiz.php">Play</a>
    <a href="leaderboard.php" class="active">Leaderboard</a>
    <a href="admin/index.php">Admin</a>
  </div>
</nav>

<div class="lb-header">
  <h1 class="lb-title glow">🏆 Leaderboard</h1>
  <p style="color:var(--muted);margin-top:.5rem;">Top <?= count($lb) ?> players ranked by score &amp; speed</p>
</div>

<div class="container">
<?php if (empty($lb)): ?>
  <div class="empty-state">
    <span class="emoji">😶</span>
    <p>No scores yet. Be the first to play!</p>
    <a href="quiz.php" class="btn btn-primary" style="margin-top:1rem">▶ Start Quiz</a>
  </div>
<?php else: ?>

  <!-- Podium (top 3) -->
  <?php if (count($lb) >= 1): ?>
  <div class="podium">
    <?php
    $medals = ['🥇','🥈','🥉'];
    $classes = ['first','second','third'];
    $show = array_slice($lb, 0, 3);
    // reorder: 2nd, 1st, 3rd for visual podium
    $order = count($show) >= 3 ? [$show[1], $show[0], $show[2]] : $show;
    foreach ($order as $i => $p):
      $orig_rank = array_search($p, $show);
    ?>
    <div class="podium-card <?= $classes[$orig_rank] ?>">
      <div class="podium-medal"><?= $medals[$orig_rank] ?></div>
      <div class="podium-name"><?= htmlspecialchars($p['name']) ?></div>
      <div class="podium-pct"><?= $p['percent'] ?>%</div>
      <div style="color:var(--muted);font-size:.8rem;margin-top:.3rem"><?= $p['score'] ?>/<?= $p['total'] ?> • <?= $p['time'] ?>s</div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Full table -->
  <div class="card" style="padding:0;overflow:hidden">
    <table class="table">
      <thead>
        <tr>
          <th>#</th>
          <th>Player</th>
          <th>Score</th>
          <th>Accuracy</th>
          <th>Time</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($lb as $i => $row): ?>
        <tr>
          <td>
            <span class="rank-num <?= $i < 3 ? "rank-badge-".($i+1) : '' ?>">
              <?= $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i+1) ?>
            </span>
          </td>
          <td style="font-weight:700"><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <span class="badge <?= $row['percent'] >= 80 ? 'badge-success' : ($row['percent'] >= 50 ? 'badge-cyan' : 'badge-danger') ?>">
              <?= $row['score'] ?>/<?= $row['total'] ?>
            </span>
          </td>
          <td style="font-weight:700;color:<?= $row['percent'] >= 80 ? 'var(--success)' : ($row['percent'] >= 50 ? 'var(--accent2)' : 'var(--danger)') ?>">
            <?= $row['percent'] ?>%
          </td>
          <td style="color:var(--muted)"><?= $row['time'] ?>s</td>
          <td style="color:var(--muted);font-size:.8rem"><?= $row['date'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="text-align:right;margin-top:1rem">
    <a href="leaderboard.php?clear=1"
       onclick="return confirm('Clear all scores?')"
       class="btn btn-ghost btn-sm" style="color:var(--danger);border-color:var(--danger)">
      🗑 Clear Board
    </a>
  </div>
<?php endif; ?>

  <div style="text-align:center;margin-top:2.5rem">
    <a href="quiz.php" class="btn btn-primary">▶ Play &amp; Compete</a>
  </div>
</div>

</body>
</html>
