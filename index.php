<?php
    $activePage = 'login';
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
            <div id='login-form'>
                <h2>Login to your account!</h2>
                <label class='input-wrapper'>
                    <img src='images/user.png' alt='password key'>
                    <input type='text' class='login-input' placeholder='Enter your email...'>
                </label>
                <label class='input-wrapper'>
                    <img src='images/key.png' alt='password key'>
                    <input type='password' class='login-input' placeholder='Enter your password...'>
                </label>
                <a href='' id='forgot-pwd'>I forgot my password</a>
                <!-- will be removed, duh -->
                <audio id='forgot-pwd-audio'>
                    <source src='audio/forgot_password.mp3' type='audio/mpeg'>
                </audio>
                <button onclick='location.href="user-homepage.php"' id='login-btn'>LOGIN</button>
            </div>
        </aside>
    </div>

    <footer>
        <p>the footer</p>
    </footer>
</body>

</html>