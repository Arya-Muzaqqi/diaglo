<?php
include '../includes/config.php';
include '../includes/functions.php';

if (!isAdmin()) {
    redirect('../login.php');
}

// Hapus user jika ada request delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
}

// Ubah role user jika ada request update
if (isset($_POST['update_role'])) {
    $id = $_POST['user_id'];
    $role = $_POST['role'];
    $conn->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $id]);
}

// Ambil semua user
$users = $conn->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola User - DIAGLO</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Kelola User</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role" onchange="this.form.submit()">
                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <input type="hidden" name="update_role" value="1">
                    </form>
                </td>
                <td>
                    <a href="?delete=<?= $user['id'] ?>" class="btn-delete" 
                       onclick="return confirm('Hapus user ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>