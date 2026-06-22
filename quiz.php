<?php
session_start();
require_once 'data.php';

// ── Handle answer submission ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'start') {
        $name = trim($_POST['player_name'] ?? '');
        if ($name === '') $name = 'Anonymous Otaku';
        $questions = get_questions();
        shuffle($questions);
        $_SESSION['quiz'] = [
            'player'    => $name,
            'questions' => $questions,
            'current'   => 0,
            'score'     => 0,
            'answers'   => [],
            'start'     => time(),
        ];
        header('Location: quiz.php');
        exit;
    }

    if ($_POST['action'] === 'answer' && isset($_SESSION['quiz'])) {
        $q     = &$_SESSION['quiz'];
        $idx   = (int)$q['current'];
        $sel   = isset($_POST['selected']) ? (int)$_POST['selected'] : -1;
        $correct = (int)$q['questions'][$idx]['answer'];
        $right = ($sel === $correct);
        if ($right) $q['score']++;
        $q['answers'][$idx] = ['selected' => $sel, 'correct' => $correct, 'right' => $right];
        $q['current']++;

        if ($q['current'] >= count($q['questions'])) {
            $time_taken = time() - $q['start'];
            save_score($q['player'], $q['score'], count($q['questions']), $time_taken);
            $_SESSION['quiz_done'] = $q;
            unset($_SESSION['quiz']);
            header('Location: result.php');
            exit;
        }
        header('Location: quiz.php');
        exit;
    }

    if ($_POST['action'] === 'reset') {
        unset($_SESSION['quiz'], $_SESSION['quiz_done']);
        header('Location: index.php');
        exit;
    }
}

// ── Determine state ───────────────────────────
$quiz = $_SESSION['quiz'] ?? null;
$show_start = !$quiz;
$current_q  = null;
if ($quiz) {
    $idx       = $quiz['current'];
    $current_q = $quiz['questions'][$idx] ?? null;
    $total     = count($quiz['questions']);
    $progress  = round(($idx / $total) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $show_start ? 'Enter Name – AniQuiz' : 'Quiz – AniQuiz' ?></title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Start screen */
    .start-wrap { min-height: 80vh; display: flex; align-items: center; justify-content: center; }
    .start-card { width: 100%; max-width: 480px; }
    .start-title { font-family:'Bangers',cursive; font-size:3rem; letter-spacing:2px; margin-bottom:.3rem; }
    .start-sub { color:var(--muted); margin-bottom:2rem; }

    /* Quiz */
    .q-meta { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.5rem; }
    .q-counter { font-weight:700; color:var(--muted); font-size:.9rem; }
    .timer-wrap { display:flex; align-items:center; gap:.6rem; }
    .timer-num {
      font-family:'Bangers',cursive; font-size:2rem; letter-spacing:1px;
      color:var(--accent2); min-width:2.5ch; text-align:center;
      transition: color .3s;
    }
    .timer-num.urgent { color:var(--danger); animation: pulse .5s infinite alternate; }
    @keyframes pulse { to { opacity:.5; } }
    .timer-ring { position:relative; width:44px; height:44px; }
    .timer-ring svg { transform:rotate(-90deg); }
    .timer-ring circle { fill:none; stroke-width:4; stroke-linecap:round; }
    .timer-bg { stroke:var(--border); }
    .timer-fg { stroke:var(--accent2); transition:stroke-dashoffset .9s linear, stroke .3s; }

    .progress-bar-outer {
      width:100%; height:6px; background:var(--border);
      border-radius:3px; margin-bottom:2rem; overflow:hidden;
    }
    .progress-bar-inner {
      height:100%; border-radius:3px;
      background:linear-gradient(90deg, var(--accent), var(--accent2));
      transition:width .4s ease;
    }

    .question-text {
      font-size:1.35rem; font-weight:700; line-height:1.5;
      margin-bottom:1.8rem;
    }
    .options { display:flex; flex-direction:column; gap:.85rem; }
    .option-btn {
      background:var(--surface); border:2px solid var(--border);
      border-radius:12px; padding:1rem 1.2rem;
      cursor:pointer; text-align:left; font-family:inherit;
      font-size:1rem; font-weight:600; color:var(--text);
      display:flex; align-items:center; gap:1rem;
      transition:all .15s;
    }
    .option-btn:hover { border-color:var(--accent); background:rgba(224,64,251,.06); transform:translateX(4px); }
    .option-btn:focus { outline:2px solid var(--accent); outline-offset:2px; }
    .opt-letter {
      width:32px; height:32px; border-radius:50%;
      background:var(--border); display:flex; align-items:center; justify-content:center;
      font-size:.85rem; font-weight:900; flex-shrink:0;
      color:var(--muted);
    }

    /* hidden submit */
    .hidden-form { display:none; }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-brand" href="index.php">⚡ AniQuiz</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="quiz.php" class="active">Play</a>
    <a href="leaderboard.php">Leaderboard</a>
    <a href="admin/index.php">Admin</a>
  </div>
</nav>

<?php if ($show_start): ?>
<!-- ── START SCREEN ── -->
<div class="start-wrap">
  <div class="card start-card">
    <p class="hero-eyebrow" style="font-size:.75rem;letter-spacing:3px;text-transform:uppercase;color:var(--accent2);margin-bottom:.5rem;">Ready, Otaku?</p>
    <h2 class="start-title glow">Enter Your Name</h2>
    <p class="start-sub">You'll have <strong style="color:var(--accent2)">15 seconds</strong> per question. Score points and claim the top spot!</p>
    <form method="POST">
      <input type="hidden" name="action" value="start">
      <div class="form-group">
        <label for="player_name">Your Name / Alias</label>
        <input type="text" id="player_name" name="player_name" class="form-control"
               placeholder="e.g. Naruto Uzumaki" maxlength="40" autofocus>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;font-size:1.1rem;padding:.9rem">
        ▶ Begin Challenge
      </button>
    </form>
  </div>
</div>

<?php elseif ($current_q): ?>
<!-- ── QUIZ SCREEN ── -->
<div class="container-sm" style="padding-top:2.5rem">
  <div class="q-meta">
    <span class="q-counter">Question <?= $idx+1 ?> / <?= $total ?></span>
    <span class="badge badge-cyan"><?= htmlspecialchars($current_q['category'] ?? 'Anime') ?></span>
    <div class="timer-wrap">
      <div class="timer-ring">
        <svg width="44" height="44" viewBox="0 0 44 44">
          <circle class="timer-bg" cx="22" cy="22" r="18"/>
          <circle class="timer-fg" id="timer-ring" cx="22" cy="22" r="18"
            stroke-dasharray="113.1"
            stroke-dashoffset="0"/>
        </svg>
      </div>
      <span class="timer-num" id="timer-num">15</span>
    </div>
  </div>

  <div class="progress-bar-outer">
    <div class="progress-bar-inner" style="width:<?= $progress ?>%"></div>
  </div>

  <div class="card">
    <p class="question-text"><?= htmlspecialchars($current_q['question']) ?></p>
    <div class="options" id="options">
      <?php
      $letters = ['A','B','C','D'];
      foreach ($current_q['options'] as $i => $opt):
      ?>
      <button class="option-btn" onclick="selectAnswer(<?= $i ?>)">
        <span class="opt-letter"><?= $letters[$i] ?></span>
        <?= htmlspecialchars($opt) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>

  <p style="text-align:center;margin-top:1.5rem;color:var(--muted);font-size:.85rem;">
    Score: <strong style="color:var(--accent)"><?= $quiz['score'] ?></strong> pts &nbsp;|&nbsp;
    Player: <strong style="color:var(--accent2)"><?= htmlspecialchars($quiz['player']) ?></strong>
  </p>
</div>

<!-- Hidden form for submission -->
<form method="POST" id="ans-form" class="hidden-form">
  <input type="hidden" name="action" value="answer">
  <input type="hidden" name="selected" id="selected-input" value="-1">
</form>

<script>
const TOTAL_TIME = 15;
let remaining = TOTAL_TIME;
let submitted  = false;
const numEl   = document.getElementById('timer-num');
const ringEl  = document.getElementById('timer-ring');
const CIRCUMFERENCE = 113.1;

function updateRing() {
  const fraction = remaining / TOTAL_TIME;
  ringEl.style.strokeDashoffset = CIRCUMFERENCE * (1 - fraction);
  if (remaining <= 5) {
    numEl.classList.add('urgent');
    ringEl.style.stroke = 'var(--danger)';
  }
}

function selectAnswer(idx) {
  if (submitted) return;
  submitted = true;
  clearInterval(timer);
  document.getElementById('selected-input').value = idx;
  // flash chosen button
  const btns = document.querySelectorAll('.option-btn');
  btns.forEach(b => b.disabled = true);
  btns[idx].style.borderColor = 'var(--accent)';
  btns[idx].style.background = 'rgba(224,64,251,.15)';
  setTimeout(() => document.getElementById('ans-form').submit(), 350);
}

updateRing();
const timer = setInterval(() => {
  remaining--;
  numEl.textContent = remaining;
  updateRing();
  if (remaining <= 0) {
    clearInterval(timer);
    if (!submitted) {
      submitted = true;
      document.getElementById('ans-form').submit();
    }
  }
}, 1000);
</script>
<?php endif; ?>

</body>
</html>
