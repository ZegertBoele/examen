<html lang="en">

<head>
    <!-- Haal bootstrap op via CDN -->
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Haal eigen CSS op -->
	<link href="./Includes/CSS/index.css" rel="stylesheet">
    <title>Aanmelden Schaatsliefhebbers</title>
</head>
<body>
    <div class="wrapper fadeInDown">
        <div id="formContent">
            <div class="fadeIn first">
                <!-- Icoon boven login scherm -->
                <img src="https://cdn0.iconfinder.com/data/icons/set-ui-app-android/32/8-512.png" id="icon" alt="User Icon" />
            </div>
            <!-- Login Form -->
            <form action="login-handler.php" method="POST" class="form_big">
                <input type="text" class="input fadeIn second" name="inputUsername" id="inputUsername" value=""
                    autocomplete="username" placeholder="Gebruikersnaam..."><br>
                <input type="password" class="input fadeIn third" name="inputPassword" id="inputPassword" value=""
                    autocomplete="current-password" placeholder="Wachtwoord..."><br>
                <input type="submit" class="input fadeIn fourth" id="submit" value="Login" name="submit"><br>
                <a href="./users/user_new.php">Nog geen account?</a>
            </form>
        </div>
</body>
</html>