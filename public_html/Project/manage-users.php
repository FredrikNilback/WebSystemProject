<?php
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'administrator') {
        header('Location: unauthorized.php');
    }

    $activePage = 'manage-users';
    require_once '../../app/db.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username  = $_POST['username'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname  = $_POST['lastname'] ?? '';
        $email     = $_POST['email'] ?? '';
        $password  = $_POST['password'] ?? '';
        $role      = $_POST['role'] ?? '';

        createUser($username, $firstname, $lastname, $email, $password, $role);
    }

    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 24;

    $allowedLimits = [12, 24, 48];

    if (!in_array($limit, $allowedLimits)) {
        $limit = 24;
    }

    $userCount = getUserCount('');
    $totalPages =  ceil($userCount / $limit);
    
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    if ($page < 1) {
        $page = 1;
    }
        
    $offset = ($page - 1) * $limit;
    $users = getUsers('', $limit, $offset);
?>

<?php require_once 'includes/header.php' ?>
    <div class='content'>
        <main class='fullscreen'>
            <h1>User management</h1>
            <div id='user-card-area'>
                <?php foreach ($users as $user): ?>
                    <div class='user-card'>
                        <div class='user-card-header'>
                            <h2><?= htmlspecialchars($user['username']) ?></h2>
                            <p><?= htmlspecialchars($user['full_name']) ?></p>

                        </div>
                    </div>
                <?php endforeach; ?>
                <div id='pag'>
                    <nav class='pagination'>
                        <a href='?page=1&limit=<?= $limit ?>'><<</a>
                        <a href='?page=<?= max(1, $page - 1); ?>&limit=<?= $limit ?>'><</a>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href='?page=<?= $i ?>&limit=<?= $limit ?>' class='<?= $i == $page ? "active" : "" ?>'><?= $i ?></a>
                        <?php endfor; ?>
                        <a href='?page=<?= min($totalPages, $page + 1); ?>&limit=<?= $limit ?>'>></a>
                        <a href='?page=<?= $totalPages ?>&limit=<?= $limit ?>'>>></a>
                    </nav>
                </div>
            </div>
            <div id='options-panel'>
                <form method='GET' id='limit-form'>
                    <label for='limit'>Users per page:</label>
                    <select name='limit' id='limit' onchange='this.form.submit()'>
                        <option value='12' <?= $limit == 12 ? 'selected' : '' ?>>12</option>
                        <option value='24' <?= $limit == 24 ? 'selected' : '' ?>>24</option>
                        <option value='48' <?= $limit == 48 ? 'selected' : '' ?>>48</option>
                    </select>
                </form>
                <button type='button' id='open-create-user-panel-btn'>Add user</button>
            </div>

        </main>
        <aside class='hidden'>
            <h1>Add new user</h1>
            <form method='POST'>
                <input type='text' name='username' placeholder='Username' required />
                <input type='text' name='firstname' placeholder='First Name' required />
                <input type='text' name='lastname' placeholder='Last Name' required />
                <input type='email' name='email' placeholder='Email' required />
                <input type='password' name='password' placeholder='Password' required />
                <select name='role' required>
                    <option value=''>Select role</option>
                    <option value='reporter'>Reporter</option>
                    <option value='responder'>Responder</option>
                    <option value='administrator'>Administrator</option>
                </select>
                <button type='submit'>Create User</button>
            </form>
        </aside>

    </div>
<?php require_once 'includes/footer.php' ?>