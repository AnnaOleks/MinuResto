<?php
include('conf.php');
session_start();
global $yhendus;
$error = "";
if (!empty($_POST['login']) && !empty($_POST['pass'])) {
    $nimi = htmlspecialchars(trim($_POST['nimi']));
    $email = htmlspecialchars(trim($_POST['email']));
    $tel = htmlspecialchars(trim($_POST['telefon']));
    $login = htmlspecialchars(trim($_POST['login']));
    $pass = htmlspecialchars(trim($_POST['pass']));
    $krypt = crypt($pass, "cool");

    $paring = $yhendus->prepare("SELECT kasutajanimi FROM Klient WHERE kasutajanimi=?");
    $paring->bind_param("s", $login);
    $paring->execute();
    $paring->store_result();

    if ($paring->num_rows > 0) {
        echo "<script>
                alert('Selline kasutajanimi on juba olemas!');
                window.location.href='registr.php';
            </script>";
    } else {
        $paring->close();
        $onadmin = 0;
        $paring = $yhendus->prepare("INSERT INTO Klient (nimi, e_post, telefon, kasutajanimi, parool, onadmin) VALUES (?, ?, ?, ?, ?, ?)");
        $paring->bind_param("sssssi", $nimi, $email, $tel, $login, $krypt, $onadmin);
        if ($paring->execute()) {
            echo "<script>
                alert('Kasutaja on registreeritud edukalt!');
                window.location.href='login.php';
            </script>";
            exit();
        } else {
            echo "<script>
                alert('Viga registreerimisel!');
                window.location.href='registr.php';
            </script>";
        }
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
    <h3>Registreerimisvorm</h3>
    <p>Palun registreeru või kui konto on olemas <a href="login.php">logi sisse</a></p>
    <br>
    <br>
    <form action="registr.php" method="post">
        <table id="regform">
            <tr>
                <td><label for="nimi">Nimi:</label></td>
                <td><input type="text" name="nimi" required></td>
            </tr>
            <tr>
                <td><label for="email">E-post:</label></td>
                <td><input type="email" name="email"></td>
            </tr>
            <tr>
                <td><label for="telefon">Telefon:</label></td>
                <td><input type="text" name="telefon"></td>
            </tr>
            <tr>
                <td><label for="login">Kasutajanimi:</label></td>
                <td><input type="text" name="login" placeholder="Kasutajanimi" required></td>
            </tr>
            <tr>
                <td><label for="pass">Parool:</label></td>
                <td><input type="password" name="pass" placeholder="********" required></td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" value="Registreeri">
                    <input type="submit" value="Logi sisse" onclick="location.href='login.php'">
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