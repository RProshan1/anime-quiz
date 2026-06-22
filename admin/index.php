<?php
require_once '../data.php';

$msg = '';
$questions = get_questions();

// ── Handle actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($_POST['action'] === 'add') {
        $q_text  = trim($_POST['question'] ?? '');
        $opts    = array_map('trim', $_POST['options'] ?? []);
        $answer  = (int)($_POST['answer'] ?? 0);
        $cat     = trim($_POST['category'] ?? 'General');

        if ($q_text && count(array_filter($opts)) === 4 && $answer >= 0 && $answer <= 3) {
            $new_id = max(array_column($questions, 'id') ?: [0]) + 1;
            $questions[] = [
                'id'       => $new_id,
                'question' => $q_text,
                'options'  => array_values($opts),
                'answer'   => $answer,
                'category' => $cat,
            ];
            save_questions($questions);
            $msg = ['type'=>'success','text'=>'Question added successfully!'];
        } else {
            $msg = ['type'=>'danger','text'=>'Fill in the question, all 4 options, and select the correct answer.'];
        }
    }

    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $del_id   = (int)$_POST['id'];
        $questions = array_filter($questions, fn($q) => $q['id'] !== $del_id);
        save_questions($questions);
        $msg = ['type'=>'success','text'=>'Question deleted.'];
        $questions = get_questions();
    }

    if ($_POST['action'] === 'edit') {
        $edit_id = (int)$_POST['id'];
        $q_text  = trim($_POST['question'] ?? '');
        $opts    = array_map('trim', $_POST['options'] ?? []);
        $answer  = (int)($_POST['answer'] ?? 0);
        $cat     = trim($_POST['category'] ?? 'General');

        foreach ($questions as &$q) {
            if ($q['id'] === $edit_id) {
                $q['question'] = $q_text;
                $q['options']  = array_values($opts);
                $q['answer']   = $answer;
                $q['category'] = $cat;
                break;
            }
        }
        save_questions($questions);
        $msg = ['type'=>'success','text'=>'Question updated!'];
        $questions = get_questions();
    }
}

$edit_q = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    foreach ($questions as $q) {
        if ($q['id'] === $eid) { $edit_q = $q; break; }
    }
}

$categories = ['Action','Adventure','Thriller','Sci-Fi','Film','Romance','Comedy','Fantasy','General'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – AniQuiz</title>
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .admin-grid { display:grid; grid-template-columns:1fr 1fr; gap:2rem; align-items:start; }
    @media(max-width:720px){ .admin-grid { grid-template-columns:1fr; } }
    .options-grid { display:grid; grid-template-columns:1fr 1fr; gap:.6rem; }
    .opt-row { display:flex; align-items:center; gap:.5rem; }
    .opt-row .opt-label { color:var(--muted); font-weight:700; font-size:.85rem; min-width:20px; }
    .correct-radio { display:flex; align-items:center; gap:.4rem; }
    .correct-radio input { accent-color:var(--accent); width:16px; height:16px; }
    .q-row { border-bottom:1px solid var(--border); padding:.85rem 0; }
    .q-row:last-child { border:none; }
    .q-text { font-weight:600; margin-bottom:.4rem; }
    .q-actions { display:flex; gap:.5rem; margin-top:.5rem; }
    .admin-header { padding:2rem 0 1rem; display:flex; align-items:center; justify-content:space-between; }
  </style>
</head>
<body>

<nav class="nav">
  <a class="nav-brand" href="../index.php">⚡ AniQuiz</a>
  <div class="nav-links">
    <a href="../index.php">Home</a>
    <a href="../quiz.php">Play</a>
    <a href="../leaderboard.php">Leaderboard</a>
    <a href="index.php" class="active">Admin</a>
  </div>
</nav>

<div class="container">
  <div class="admin-header">
    <h1 class="page-title" style="font-size:2.5rem">⚙️ Admin Panel</h1>
    <span class="badge badge-cyan"><?= count($questions) ?> Questions</span>
  </div>

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msg['type'] ?>"><?= $msg['text'] ?></div>
  <?php endif; ?>

  <div class="admin-grid">

    <!-- ── ADD / EDIT FORM ── -->
    <div>
      <div class="card">
        <h3 style="font-family:'Bangers',cursive;font-size:1.6rem;letter-spacing:1px;margin-bottom:1.2rem">
          <?= $edit_q ? '✏️ Edit Question' : '➕ Add Question' ?>
        </h3>
        <form method="POST">
          <input type="hidden" name="action" value="<?= $edit_q ? 'edit' : 'add' ?>">
          <?php if ($edit_q): ?>
          <input type="hidden" name="id" value="<?= $edit_q['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label>Question</label>
            <textarea name="question" class="form-control" rows="3" required
              placeholder="Which anime character wields a Death Note?"
            ><?= htmlspecialchars($edit_q['question'] ?? '') ?></textarea>
          </div>

          <div class="form-group">
            <label>Answer Options</label>
            <div style="display:flex;flex-direction:column;gap:.6rem">
              <?php
              $letters = ['A','B','C','D'];
              for ($i=0;$i<4;$i++):
                $val = htmlspecialchars($edit_q['options'][$i] ?? '');
              ?>
              <div class="opt-row">
                <span class="opt-label"><?= $letters[$i] ?></span>
                <input type="text" name="options[]" class="form-control" value="<?= $val ?>"
                       placeholder="Option <?= $letters[$i] ?>" required style="flex:1">
                <label class="correct-radio">
                  <input type="radio" name="answer" value="<?= $i ?>"
                    <?= (isset($edit_q) && $edit_q['answer'] === $i) ? 'checked' : '' ?>>
                  ✔
                </label>
              </div>
              <?php endfor; ?>
            </div>
            <p style="color:var(--muted);font-size:.8rem;margin-top:.5rem">✔ = mark the correct answer</p>
          </div>

          <div class="form-group">
            <label>Category</label>
            <select name="category" class="form-control">
              <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>" <?= ($edit_q['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="display:flex;gap:.7rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-primary">
              <?= $edit_q ? '💾 Save Changes' : '➕ Add Question' ?>
            </button>
            <?php if ($edit_q): ?>
            <a href="index.php" class="btn btn-ghost">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- ── QUESTION LIST ── -->
    <div>
      <div class="card" style="max-height:75vh;overflow-y:auto">
        <h3 style="font-family:'Bangers',cursive;font-size:1.6rem;letter-spacing:1px;margin-bottom:1rem">
          📋 All Questions
        </h3>
        <?php if (empty($questions)): ?>
          <p style="color:var(--muted)">No questions yet. Add some!</p>
        <?php endif; ?>
        <?php foreach ($questions as $q): ?>
        <div class="q-row">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem">
            <p class="q-text"><?= htmlspecialchars($q['question']) ?></p>
            <span class="badge badge-accent" style="flex-shrink:0"><?= htmlspecialchars($q['category']) ?></span>
          </div>
          <p style="color:var(--success);font-size:.82rem">
            ✔ <?= htmlspecialchars($q['options'][$q['answer']] ?? '—') ?>
          </p>
          <div class="q-actions">
            <a href="index.php?edit=<?= $q['id'] ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
            <form method="POST" style="display:inline" onsubmit="return confirm('Delete this question?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $q['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

</body>
</html>
