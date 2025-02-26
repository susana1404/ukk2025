<?php
// Koneksi ke database
$koneksi = mysqli_connect('localhost', 'root', '', 'tugas_ukk');

// Edit List
if (isset($_POST['edit_list'])) {
    $list_id = intval($_POST['list_id']);
    $list_name = mysqli_real_escape_string($koneksi, $_POST['list_name']);
    mysqli_query($koneksi, "UPDATE lists SET name='$list_name' WHERE id='$list_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Hapus List
if (isset($_GET['delete_list'])) {
    $list_id = intval($_GET['delete_list']);
    mysqli_query($koneksi, "DELETE FROM lists WHERE id='$list_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Tambah Task
if (isset($_POST['add_task'])) {
    $task = mysqli_real_escape_string($koneksi, $_POST['task']);
    $list_id = intval($_POST['list_id']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    
    mysqli_query($koneksi, "INSERT INTO tasks (list_id, task, priority, due_date, status) 
                            VALUES ('$list_id', '$task', '$priority', '$due_date', '0')");
    echo "<script>window.location.href='index.php';</script>";
}

// Edit Task
if (isset($_POST['edit_task'])) {
    $task_id = $_POST['task_id'];
    $task = mysqli_real_escape_string($koneksi, $_POST['task']);
    $priority = $_POST['priority'];
    $due_date = $_POST['due_date'];
    
    mysqli_query($koneksi, "UPDATE tasks SET task='$task', priority='$priority', due_date='$due_date' WHERE id='$task_id'");
   //mysqli_query($koneksi, "UPDATE tasks SET task='$task' WHERE id='$task_id'");
   echo "<script>window.location.href='index.php';</script>";
}

// Hapus Task
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    mysqli_query($koneksi, "DELETE FROM tasks WHERE id='$task_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Tandai Task Selesai
if (isset($_GET['done_task'])) {
    $task_id = $_GET['done_task'];
    mysqli_query($koneksi, "UPDATE tasks SET status='1' WHERE id='$task_id'");
    echo "<script>window.location.href='index.php';</script>";
}

// Ambil List
$lists = mysqli_query($koneksi, "SELECT * FROM lists");

// Ambil Task
$tasks = mysqli_query($koneksi, "SELECT tasks.*, lists.name AS list_name FROM tasks 
                                 JOIN lists ON tasks.list_id = lists.id 
                                 ORDER BY FIELD(status, '0', '1'), priority DESC, due_date ASC");
?>

<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
</head>
<body>
<nav class="navbar shadhow-lg fixed-top">
<div class="container">
    <a class="navbar-brand fw-bold" href="#">To Do List Apps</a>
    <div class="ms-auto">
        <a href="index.php" class="text-decoration-none mx-3">Home</a>
        <a href="tambah_list.php" class="text-decoration-none mx-3">Tambah List</a>
        <a href="logout.php" style="text-decoration: none; color: red; font-weight: bold;">Logout</a>

    </div>
</nav>

<div class="container mt-3">   
    <div class="row">
        
        <!-- Tambah Task -->
        <div class="container-fluid tambah-list pt-5 pb-5">
     <div class="container text-center"> <br>
            <div class="border p-3 bg-light">
                <h5>Tambah Task</h5>
                <form method="POST">
                    <select name="list_id" class="form-control" required>
                        <option value="">Pilih List</option>
                        <?php while ($list = mysqli_fetch_assoc($lists)) : ?>
                            <option value="<?= $list['id']; ?>"><?= $list['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" name="task" class="form-control mt-2" placeholder="Masukkan task" required>
                    <select name="priority" class="form-control mt-2">
                        <option value="1">Low</option>
                        <option value="2">Medium</option>
                        <option value="3">High</option>
                    </select>
                    <input type="date" name="due_date" class="form-control mt-2" required>
                    <button type="submit" class="btn btn-success w-100 mt-2" name="add_task">Tambah Task</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Task -->
    <div class="border p-3 bg-light mt-3">
        <h5>Daftar Task</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>List</th>
                    <th>Task</th>
                    <th>Prioritas</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while ($task = mysqli_fetch_assoc($tasks)) : ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= $task['list_name']; ?></td>
                        <td><?= $task['task']; ?></td>
                        <td><?= ($task['priority'] == 1) ? 'Low' : (($task['priority'] == 2) ? 'Medium' : 'High'); ?></td>
                        <td><?= $task['due_date']; ?></td>
                        <td>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editTaskModal<?= $task['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="?delete_task=<?= $task['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus task ini?')">Delete</a>

                            <?php if ($task['status'] == 0) : ?>
                                <a href="?done_task=<?= $task['id']; ?>" class="btn btn-success btn-sm" style="background-color: blue;">Selesai</a>
                            <?php else: ?>
                                <span class="badge bg-success ">Selesai</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                   <!-- Modal Edit Task -->
<div class="modal fade" id="editTaskModal<?= $task['id']; ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="task_id" value="<?= $task['id']; ?>">
                    
                    <!-- Input Nama Task -->
                    <input type="text" name="task" class="form-control" value="<?= $task['task']; ?>" required>

                    <!-- Pilih List -->
                    <select name="list_id" class="form-control mt-2" required>
                        <?php
                        $list_options = mysqli_query($koneksi, "SELECT * FROM lists");
                        while ($list = mysqli_fetch_assoc($list_options)) : ?>
                            <option value="<?= $list['id']; ?>" <?= ($list['id'] == $task['list_id']) ? 'selected' : ''; ?>>
                                <?= $list['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <!-- Pilih Prioritas -->
                    <select name="priority" class="form-control mt-2">
                        <option value="1" <?= ($task['priority'] == 1) ? 'selected' : ''; ?>>Low</option>
                        <option value="2" <?= ($task['priority'] == 2) ? 'selected' : ''; ?>>Medium</option>
                        <option value="3" <?= ($task['priority'] == 3) ? 'selected' : ''; ?>>High</option>
                    </select>

                    <!-- Pilih Tanggal -->
                    <input type="date" name="due_date" class="form-control mt-2" value="<?= $task['due_date']; ?>" required>

                    <button type="submit" class="btn btn-primary mt-2 w-100" name="edit_task">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>