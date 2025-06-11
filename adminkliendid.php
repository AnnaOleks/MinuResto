<?php
require_once("conf.php");
session_start();
require_once("functions.php");
global $yhendus;
global $kasutajanimi;
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = false;
}
if (!isset($_SESSION['klient'])) {
    $_SESSION['klient'] = false;
}
$sorttulp="nimi";
$otsisona="";
if(isSet($_REQUEST["sort"])){
    $sorttulp=$_REQUEST["sort"];
}
if(isSet($_REQUEST["otsisona"])){
    $otsisona=$_REQUEST["otsisona"];
}
if (isset($_POST["lisamine"]) && isAdmin()) {
    $nimi = htmlspecialchars(trim($_POST["nimi"]));
    $e_post = htmlspecialchars(trim($_POST["e_post"]));
    $telefon = htmlspecialchars(trim($_POST["telefon"]));
    $kasutajanimi = htmlspecialchars(trim($_POST["kasutajanimi"]));
    $parool = crypt(htmlspecialchars(trim($_POST["parool"])), "cool");
    $onadmin = isset($_POST["onadmin"]) ? intval($_POST["onadmin"]) : 0;

    // Проверяем, есть ли такой клиент уже
    $paring = $yhendus->prepare("SELECT klient_id FROM Klient WHERE kasutajanimi=?");
    $paring->bind_param("s", $kasutajanimi);
    $paring->execute();
    $paring->bind_result($existing_id);

    if ($paring->fetch()) {
        $paring->close();
        // Пользователь уже существует — показываем alert и не добавляем
        echo "<script>alert('Kasutajanimi \"$kasutajanimi\" on juba olemas!'); window.location='adminkliendid.php';</script>";
        exit();
    } else {
        $paring->close();
        $insert = $yhendus->prepare("INSERT INTO Klient (kasutajanimi, parool, nimi, e_post, telefon, onadmin) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("sssssi", $kasutajanimi, $parool, $nimi, $e_post, $telefon, $onadmin);
        $insert->execute();
        $insert->close();

        header("Location: adminkliendid.php?success=1");
        exit();
    }
}
if (isset($_POST["muutmine"]) && isAdmin()) {
    muudaKlient($_POST["muudetudid"], $_POST["nimi"], $_POST["e_post"], $_POST["telefon"], $_POST["kasutajanimi"], $_POST["parool"], $_POST["onadmin"]);
    header("Location: adminkliendid.php");
    exit();
}
$klientideNimekiri = kysiKlient($sorttulp, $otsisona);
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="utf-8">
    <title>MinuResto</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<?php if (isset($_GET['success'])): ?>
    <script>alert('Lisamine õnnestus!');</script>
<?php endif; ?>
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
<br>
<?php if (isAdmin()): ?>
    <div id="adminnav">
        <a href="broneering.php">Broneeringud</a>
        <a href="adminkliendid.php">Kliendid</a>
        <a href="adminmenuu.php">Menüü</a>
    </div>
<?php endif; ?>
<br>
<h4>Kliendid</h4>
<br>

<div class="broncontainer">
    <div class="flexcontainer">
        <div id="tabel">
            <form action="adminkliendid.php">
                <label for="otsisona">Otsing:</label>
                <input type="text" name="otsisona" id="otsisona" placeholder="Sisesta otsingusõna">
            </form>
            <br>
            <h4>Klientide loetelu</h4>
            <table id="menuulist">
                <tr>
                    <td>Haldus</td>
                    <td><a href="adminkliendid.php?sort=nimi">Nimi</a></td>
                    <td><a href="adminkliendid.php?sort=e_post">E_post</a></td>
                    <td><a href="adminkliendid.php?sort=telefon">Telefon</a></td>
                    <td><a href="adminkliendid.php?sort=kasutajanimi">Kasutajanimi</a></td>
                    <td><a href="adminkliendid.php?sort=parool">Parool</a></td>
                    <td><a href="adminkliendid.php?sort=onadmin">Admin</a></td>
                </tr>

                <?php foreach ($klientideNimekiri  as $klient): ?>
                    <?php if (isset($_GET["muutmisid"]) && intval($_GET["muutmisid"]) == $klient->klient_id && isAdmin()): ?>
                        <tr>
                            <td colspan="7">
                                <form action="adminkliendid.php" method="post" >
                                    <input type="hidden" name="muudetudid" value="<?= $klient->klient_id ?>">
                                    <input type="text" name="nimi" value="<?= htmlspecialchars($klient->nimi)?>">
                                    <input type="email" name="e_post" value="<?= $klient->e_post ?>">
                                    <br>
                                    <input type="tel" name="telefon" value="<?= $klient->telefon ?>">
                                    <input type="text" name="kasutajanimi" value="<?= $klient->kasutajanimi ?>">
                                    <br>
                                    <input type="password" name="parool" value="<?= $klient->parool ?>">
                                    <input type="text" name="onadmin" value="<?= $klient->onadmin ?>">
                                    <br>
                                    <input type="submit" name="muutmine" value="Muuda">
                                    <input type="submit" name="katkestus" value="Katkesta" onclick="window.location='adminkliendid.php'; return false;">
                                </form>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>
                                <?php if (isAdmin()): ?>
                                    <a href="adminkliendid.php?muutmisid=<?= $klient->klient_id ?>">m</a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($klient->nimi) ?></td>
                            <td><?= $klient->e_post ?></td>
                            <td><?= $klient->telefon ?></td>
                            <td><?= $klient->kasutajanimi ?></td>
                            <td><?= $klient->parool ?></td>
                            <td><?= $klient->onadmin ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
        <div id="forms">
            <?php if (isAdmin()): ?>
            <h4>
                Tere,
                <?php
                if (isAdmin()) {
                    echo "Administraator";
                } else {
                    echo htmlspecialchars($_SESSION['klient']);
                }
                ?>
                !
            </h4>
            <form action="logout.php" method="post">
                <input type="submit" name="logout" value="Logi välja">
            </form>
            <br>
            <br>
            <h4>Lisa uus klient</h4>
            <form action="adminkliendid.php" method="post">
                <table id="lisaklient">
                    <?php if (isAdmin()): ?>
                        <tr>
                            <td><label for="nimi">Nimi:</label></td>
                            <td><input type="text" name="nimi" required></td>
                        </tr>
                        <tr>
                            <td><label for="e_post">E-post:</label></td>
                            <td><input type="email" name="e_post" required></td>
                        </tr>
                        <tr>
                            <td><label for="telefon">Telefon:</label></td>
                            <td><input type="tel" name="telefon" required></td>
                        </tr>
                        <tr>
                            <td><label for="kasutajanimi">Kasutajanimi:</label></td>
                            <td><input type="text" name="kasutajanimi" required></td>
                        </tr>
                        <tr>
                            <td><label for="parool">Parool:</label></td>
                            <td><input type="password" name="parool" required></td>
                        </tr>
                        <tr>
                            <td><label for="onadmin">Admin:</label></td>
                            <td><input type="number" name="onadmin" min="0" max="1" required></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="lisamine" id="lisamine" value="Lisa"></td>
                    </tr>
                </table>
            </form>
        </div>
    </div>
</div>
<?php else: ?>
    <p>Palun <a href="login.php">logi sisse</a>.</p>
<?php endif; ?>
<footer>
    <br>
    <br>
    annaoleks88@gmail.com
    <br>
    Anna Oleks   &copy; 2025
</footer>
</body>
</html>