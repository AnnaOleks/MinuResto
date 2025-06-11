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
$sorttulp="kuupaev";
$otsisona="";
if(isSet($_REQUEST["sort"])){
    $sorttulp=$_REQUEST["sort"];
}
if(isSet($_REQUEST["otsisona"])){
    $otsisona=$_REQUEST["otsisona"];
}
// Обработка добавления бронирования
if (
    isset($_POST["broneeri"]) &&
    !empty($_POST["nimi"]) &&
    !empty($_POST["e_post"]) &&
    !empty($_POST["laud"]) &&
    !empty($_POST["kuupaev"]) &&
    !empty($_POST["kellaaeg"]) &&
    !empty($_POST["inimeste_arv"])
) {
    $nimi = htmlspecialchars(trim($_POST["nimi"]));
    $e_post = htmlspecialchars(trim($_POST["e_post"]));
    $laud = htmlspecialchars(trim($_POST["laud"]));
    $kuupaev = htmlspecialchars(trim($_POST["kuupaev"]));
    $kellaaeg = htmlspecialchars(trim($_POST["kellaaeg"]));
    $inimeste_arv = intval($_POST["inimeste_arv"]);

    if (isAdmin()) {
        $login = trim($_POST['login']);
        $telefon = htmlspecialchars(trim($_POST['telefon'])); // новое поле

        // Ищем клиента
        $paring = $yhendus->prepare("SELECT klient_id FROM Klient WHERE kasutajanimi=?");
        $paring->bind_param("s", $login);
        $paring->execute();
        $paring->bind_result($klient_id);

        if (!$paring->fetch()) {
            $paring->close();

            // Новый клиент — регистрируем
            $default_password = crypt($login, "cool"); // пароль = логин

            $insert = $yhendus->prepare("INSERT INTO Klient (kasutajanimi, parool, nimi, e_post, telefon, onadmin) VALUES (?, ?, ?, ?, ?, 0)");
            $insert->bind_param("sssss", $login, $default_password, $nimi, $e_post, $telefon);
            $insert->execute();
            $insert->close();

            // Снова получаем ID клиента
            $paring = $yhendus->prepare("SELECT klient_id FROM Klient WHERE kasutajanimi=?");
            $paring->bind_param("s", $login);
            $paring->execute();
            $paring->bind_result($klient_id);
            $paring->fetch();
        }
        $paring->close();
    } else {
        // Для клиента логин берём из сессии
        $login = $_SESSION['klient'];
        $paring = $yhendus->prepare("SELECT klient_id FROM Klient WHERE kasutajanimi=?");
        $paring->bind_param("s", $login);
        $paring->execute();
        $paring->bind_result($klient_id);
        $paring->fetch();
        $paring->close();
    }

    // Добавляем бронирование
    $stmt = $yhendus->prepare("INSERT INTO Broneering (klient_id, laud, kuupaev, kellaaeg, inimeste_arv) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iissi", $klient_id, $laud, $kuupaev, $kellaaeg, $inimeste_arv);
    $stmt->execute();
    $stmt->close();

    header("Location: broneering.php?success=1");
    exit();
}
if (isset($_GET["kustutusid"]) && isAdmin()) {
    kustutaBroneering($_GET["kustutusid"]);
    header("Location: broneering.php");
    exit();
}
if (isset($_POST["muutmine"]) && isAdmin()) {
    muudaBroneering($_POST["muudetudid"], $_POST["laud"], $_POST["kuupaev"], $_POST["kellaaeg"], $_POST["inimeste_arv"]);
    header("Location: broneering.php");
    exit();
}

// Загрузка бронирований
$broneeringud = kysiBroneering($sorttulp, $otsisona);
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
    <script>alert('Broneering õnnestus!');</script>
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
<h4>Broneeringud</h4>
<br>

<div class="broncontainer">
    <div class="flexcontainer">
        <div id="tabel">
            <form action="broneering.php">
                <label for="otsisona">Otsing:</label>
                <input type="text" name="otsisona" id="otsisona" placeholder="Sisesta otsingusõna">
            </form>
            <br>
            <h4>Broneeringute loetelu</h4>
            <table id="bronlist">
                <tr>
                    <td>Haldus</td>
                    <td><a href="broneering.php?sort=nimi">Nimi</a></td>
                    <td><a href="broneering.php?sort=e_post">E-post</a></td>
                    <td><a href="broneering.php?sort=laud">Laud</a></td>
                    <td><a href="broneering.php?sort=kuupaev">Kuupäev</a></td>
                    <td><a href="broneering.php?sort=kellaaeg">Kellaaeg</a></td>
                    <td><a href="broneering.php?sort=inimeste_arv">Inimeste arv</a></td>
                </tr>

                <?php foreach ($broneeringud as $bron): ?>
                    <?php if (isset($_GET["muutmisid"]) && intval($_GET["muutmisid"]) == $bron->broneering_id && isAdmin()): ?>
                        <tr>
                            <td colspan="7">
                                <form action="broneering.php" method="post" >
                                    <input type="hidden" name="muudetudid" value="<?= $bron->broneering_id ?>">
                                    <input type="text" name="nimi" value="<?= htmlspecialchars($bron->nimi)?>">
                                    <select name="laud">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($bron->laud == $i ? "selected" : "") ?>>Laud <?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <br>
                                    <input type="date" name="kuupaev" value="<?= $bron->kuupaev ?>">
                                    <input type="time" name="kellaaeg" value="<?= $bron->kellaaeg ?>">
                                    <br>
                                    <input type="number" name="inimeste_arv" value="<?= $bron->inimeste_arv ?>">
                                    <input type="submit" name="muutmine" value="Muuda">
                                    <input type="submit" name="katkestus" value="Katkesta" onclick="window.location='broneering.php'; return false;">
                                </form>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>
                                <?php if (isAdmin()): ?>
                                    <a href="broneering.php?kustutusid=<?= $bron->broneering_id ?>" onclick="return confirm('Kustutada broneering?')">x</a>
                                    <a href="broneering.php?muutmisid=<?= $bron->broneering_id ?>">m</a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($bron->nimi) ?></td>
                            <td><?= $bron->e_post ?></td>
                            <td><?= $bron->laud ?></td>
                            <td><?= $bron->kuupaev ?></td>
                            <td><?= $bron->kellaaeg ?></td>
                            <td><?= $bron->inimeste_arv ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
        <div id="forms">
            <?php if (isAdmin() || isKlient()): ?>
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
                <h4>Lisa uus broneering</h4>
                <form action="broneering.php" method="post">
                    <table id="lisabron">
                        <?php if (isAdmin() || isKlient()): ?>
                            <tr>
                                <td><label for="login">Kasutajanimi:</label></td>
                                <td><input type="text" name="login" required></td>
                            </tr>
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
                                <td><input type="text" name="telefon" required></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><label for="laud">Laud:</label></td>
                            <td>
                                <select name="laud" required>
                                    <?php for ($i = 1; $i <= 10; $i++): ?>
                                        <option value="<?= $i ?>">Laud <?= $i ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="kuupaev">Kuupäev:</label></td>
                            <td><input type="date" name="kuupaev" required></td>
                        </tr>
                        <tr>
                            <td><label for="kellaaeg">Kellaaeg:</label></td>
                            <td><input type="time" name="kellaaeg" required></td>
                        </tr>
                        <tr>
                            <td><label for="inimeste_arv">Inimeste arv:</label></td>
                            <td><input type="number" name="inimeste_arv" required></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="submit" name="broneeri" id="broneeri" value="Broneeri"></td>
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