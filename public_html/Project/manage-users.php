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

    $users = getUsers('', 20, 0);
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
            </div>
            <div id='options-panel'>
                <button id='add-user-btn'>Add user</button>
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