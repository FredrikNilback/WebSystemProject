<?php
    $activePage = 'manage-users';
    require_once '../../app/db.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username  = $_POST['username'] ?? '';
        $firstname = $_POST['firstname'] ?? '';
        $lastname  = $_POST['lastname'] ?? '';
        $email     = $_POST['email'] ?? '';
        $password  = $_POST['password'] ?? '';
        $role      = $_POST['role'] ?? '';

        //createUser($username, $firstname, $lastname, $email, $password, $role);
    }
?>

<?php require_once 'includes/header.php' ?>
    <div class='content'>
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
    </div>
<?php require_once 'includes/footer.php' ?>