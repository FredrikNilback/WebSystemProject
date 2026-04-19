<?php
    session_start();
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'administrator') {
        header('Location: unauthorized.php');
        exit;
    }

    $activePage = 'manage-users';
    require_once '../../app/db.php';
    updateLastSeen($_SESSION['user_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if ($_SESSION['user_role'] != 'administrator') {
            http_response_code(401);
            exit();
        }

        $action = $_POST['action'];

        switch ($action) {
            case 'create-user':
                $username  = $_POST['username'] ?? '';
                $firstname = $_POST['firstname'] ?? '';
                $lastname  = $_POST['lastname'] ?? '';
                $email     = $_POST['email'] ?? '';
                $password  = $_POST['password'] ?? '';
                $role      = $_POST['role'] ?? '';
                createUser($username, $firstname, $lastname, $email, $password, $role);
                updateLastSeen($_SESSION['user_id']);
                break;
            case 'update-role':
                $userId = (int)$_POST['user_id'];
                $newRole = $_POST['new_role'] ?? '';
                updateUserRole($userId, $newRole);
                updateLastSeen($_SESSION['user_id']);
                break;
            case 'delete-user':
                $userId = (int)$_POST['user_id'];
                deleteUser($userId);
                updateLastSeen($_SESSION['user_id']);
                break;
            default:
                http_response_code(400);
                exit();
        }
    }

    $page = $_GET['page'] ?? 1;
    $limit = $_GET['limit'] ?? 24;

    $allowedLimits = [12, 24, 48];

    if (!in_array($limit, $allowedLimits)) {
        $limit = 24;
    }

    $userCount = getUserCount();
    $totalPages =  ceil($userCount / $limit);
    
    if ($page > $totalPages) {
        $page = $totalPages;
    }
    if ($page < 1) {
        $page = 1;
    }
        
    $offset = ($page - 1) * $limit;
    $users = getUsers($limit, $offset);
    $now = time();
?>

<?php require_once 'includes/header.php' ?>
    <div class='content'>
        <main class='fullscreen'>
            <div id='user-card-area'>
                <h1>User management</h1>
                <div id='user-cards'>
                    <?php foreach ($users as $user): ?>
                        <div class='user-card'>
                            <?php if($user['user_id'] == $_SESSION['user_id']): ?>
                            <div class='user-card-header you'>
                            <?php elseif($now - strtotime($user['last_seen']) <= 600): ?>
                            <div class='user-card-header online'>
                            <?php elseif($now - strtotime($user['last_seen']) <= 1800): ?>
                            <div class='user-card-header idle'>
                            <?php else: ?>
                            <div class='user-card-header offline'>
                            <?php endif; ?>
                                <h2><?= htmlspecialchars($user['username']) ?> <?php if($user['user_id'] === $_SESSION['user_id']) {echo '(me)';} ?></h2>
                                <p><?= htmlspecialchars($user['user_role']) ?></p>
                            </div>
                            <div class='user-card-info'>
                                <p>Name: <?= $user['full_name'] ?></p>
                                <p>E-mail: <?= $user['user_email'] ?></p>
                                <p>ID: <?= $user['user_id'] ?></p>
                            </div>
                            <div class='user-card-options'>
                                <form method='POST'>
                                    <input type='hidden' name='action' value='update-role'>
                                    <input type='hidden' name='user_id' value='<?= $user['user_id'] ?>'>
                                    <select name='new_role' class='role-select'>
                                        <option value='reporter' <?php if($user['user_role'] === 'reporter') {echo 'selected="selected"';} ?>>Reporter</option>
                                        <option value='responder' <?php if($user['user_role'] === 'responder') {echo 'selected="selected"';} ?>>Responder</option>
                                        <option value='administrator' <?php if($user['user_role'] === 'administrator') {echo 'selected="selected"';} ?>>Administrator</option>
                                    </select>
                                    <button type='submit' class='update-role-btn' disabled>Update role</button>
                                </form>
                                <form class='delete-user-form' method='POST' onsubmit='return confirm("Are you sure you want to delete user: <?= htmlspecialchars($user["username"]) ?>?");'>
                                    <input type='hidden' name='action' value='delete-user'>
                                    <input type='hidden' name='user_id' value='<?= $user['user_id'] ?>'>
                                    <button type='submit' class='delete-user-btn'>Delete User</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id='pagination-div'>
                    <nav class='pagination'>
                        <a href='?page=1&limit=<?= $limit ?>' id='back-to-start'><<</a>
                        <a href='?page=<?= max(1, $page - 1); ?>&limit=<?= $limit ?>' id='next'><</a>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href='?page=<?= $i ?>&limit=<?= $limit ?>' class='<?= $i == $page ? "active" : "" ?>'><?= $i ?></a>
                        <?php endfor; ?>
                        <a href='?page=<?= min($totalPages, $page + 1); ?>&limit=<?= $limit ?>' id='previous'>></a>
                        <a href='?page=<?= $totalPages ?>&limit=<?= $limit ?>' id='skip-to-end'>>></a>
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
                <input type='hidden' name='action' value='create-user'>
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