<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?error=Silakan login dulu.");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$query = mysqli_query(
    $conn,
    "SELECT * FROM journals 
     WHERE journal_id = $id AND user_id = $user_id 
     LIMIT 1"
);

$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

$error = $_GET['error'] ?? '';
?>

<div class="container">
    <div class="card">
        <h2>Edit Jurnal</h2>

        <?php if ($error): ?>
            <p style="color:#E74C3C;"><?= htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="../process/journal_process.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="journal_id" value="<?= (int)$data['journal_id']; ?>">

            <div class="form-group">
                <label>Judul</label>
                <input type="text" name="title" maxlength="150"
                       value="<?= htmlspecialchars($data['title']); ?>">
            </div>

            <div class="form-group">
                <label>Isi Jurnal</label>
                <textarea name="content" required><?= htmlspecialchars($data['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Mood</label>
                <select name="mood" required>
                    <?php
                    $moods = ['senang','netral','sedih','cemas','marah'];
                    foreach ($moods as $m):
                        $selected = ($data['mood'] === $m) ? 'selected' : '';
                    ?>
                        <option value="<?= $m; ?>" <?= $selected; ?>>
                            <?= ucfirst($m); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn">Update</button>
            <a href="index.php" class="btn btn-secondary" style="margin-left:10px;">
                Batal
            </a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
