<?php
    session_start();
    $activePage = 'login';
    require_once '../../app/db.php';
    $error = NULL;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username  = $_POST['username'] ?? '';
        $password  = $_POST['password'] ?? '';

        $user = login($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user->user_id;
            $_SESSION['user_role'] = $user->user_role;
            $_SESSION['first_name'] = $user->first_name;
            $_SESSION['last_name'] = $user->last_name;
            $_SESSION['username'] = $user->username;
            $_SESSION['user_email'] = $user->user_email;

            header('Location: user-homepage.php');
            exit;
        }
        else {
            $error = 'Incorrect login information';
        }
    }
?>

<?php require_once 'includes/header.php' ?>
    <div class='content'>
        <main>
            <img src="" alt="A very cool image">
        </main>
        <aside>
            <div id='greeting-div'>
                <h1 id='greeting'>Greetings!</h1>
                <img src="images/time_of_day/morning.png" id='time-of-day-img' alt="time of day img">
            </div>
            <div id='login-div'>
                <h2>Login to your account!</h2>
                <form method='POST'>
                    <label class='input-wrapper'>
                        <img src='images/user.png' alt='password key'>
                        <input type='text' name='username' class='login-input' placeholder='Enter your username...'>
                    </label>
                    <label class='input-wrapper'>
                        <img src='images/key.png' alt='password key'>
                        <input type='password' name='password' class='login-input' placeholder='Enter your password...'>
                    </label>
                    <a href='' id='forgot-pwd'>I forgot my password</a>
                    <!-- will be removed, duh -->
                    <audio id='forgot-pwd-audio'>
                        <source src='audio/forgot_password.mp3' type='audio/mpeg'>
                    </audio>
                    <button type='submit' name='login' id='login-btn'>LOGIN</button>
                </form>
                <?php if ($error): ?>
                    <p id='login-error-msg'><?=$error?></p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
<?php require_once 'includes/footer.php' ?>