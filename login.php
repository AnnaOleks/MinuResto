<?php include('conf.php'); ?>
<?php
session_start();
global $yhendus;
global $onadmin;
if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    //eemaldame kasutaja sisestusest kahtlase pahna
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    //SIIA UUS KONTROLL
    $sool = 'cool';
    $krypt = crypt($pass, $sool);
    //kontrollime kas andmebaasis on selline kasutaja ja parool
    $paring = $yhendus->prepare("SELECT kasutajanimi, parool, onadmin FROM Klient WHERE kasutajanimi=? AND parool=?");
    $paring->bind_param('ss', $login, $krypt);
    $paring->execute();
    $paring->bind_result( $kasutajanimi, $parool, $onadmin);
    if ($paring->fetch() && $parool == $krypt) {
        if ($onadmin == 1) {
            $_SESSION['admin'] = true;
            $_SESSION['klient'] = false;
        } else {
            $_SESSION['admin'] = false;
            $_SESSION['klient'] = $kasutajanimi;
        }
        header("Location: broneering.php");
        exit();
    } else {
        echo "<script>
                alert('Kasutaja või parool on vale!');
            </script>";
    }
    $paring->close();
    $yhendus->close();
}
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <title>MinuResto</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<header>
    <div id="headerPHP">
        <div id="title"><h3>MinuResto</h3></div>
        <div id="nav">
            <nav>
                <ul class="menu">
                    <li>
                        <a href="avaleht.php">Avaleht</a>
                    </li>
                    <li>
                        <a href="menuu.php">Menüü</a>
                    </li>
                    <li>
                        <a class="active" href="login.php">Login</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
<br>
<div class="container">
    <h3>Laua broneerimiseks</h3>
    <p>Palun logi sisse või <a href="registr.php">registreeru</a></p>
    <br>
    <br>
    <form action="" method="post">
        <table id="loginform">
            <tr>
                <td><label for="login">Login:</label></td>
                <td><input type="text" id="login" name="login" required></td>
            </tr>
            <tr>
                <td><label for="pass">Password:</label></td>
                <td><input type="password" id="pass" name="pass" required></td>
            </tr>
            <tr>
                <td>
                    <p> </p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" value="Logi sisse">
                    <input type="button" value="Registreeri" onclick="location.href='registr.php'">
                </td>
            </tr>
        </table>
    </form>
</div>
<footer>
    <br>
    <br>
    annaoleks88@gmail.com
    <br>
    Anna Oleks   &copy; 2025
</footer>
</body>
</html>