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
$sorttulp="kategooria";
$otsisona="";
if(isSet($_REQUEST["sort"])){
    $sorttulp=$_REQUEST["sort"];
}
if(isSet($_REQUEST["otsisona"])){
    $otsisona=$_REQUEST["otsisona"];
}
if (isset($_GET["kustutusid"]) && isAdmin()) {
    kustutaRoog($_GET["kustutusid"]);
    header("Location: adminmenuu.php");
    exit();
}
if (isset($_POST["lisa"]) && isAdmin()) {
    $nimetus = htmlspecialchars(trim($_POST["nimetus"]));
    $kirjeldus = htmlspecialchars(trim($_POST["kirjeldus"]));
    $kategooria = htmlspecialchars(trim($_POST["kategooria"]));
    $hind = floatval($_POST["hind"]);

    $kask = $yhendus->prepare("INSERT INTO Menuu (nimetus, kirjeldus, kategooria, hind) VALUES (?, ?, ?, ?)");
    $kask->bind_param("sssd", $nimetus, $kirjeldus, $kategooria, $hind);
    $kask->execute();
    $kask->close();

    header("Location: adminmenuu.php");
    exit();
}
if (isset($_POST["muutmine"]) && isAdmin()) {
    muudaRoog($_POST["muudetudid"], $_POST["nimetus"], $_POST["kirjeldus"], $_POST["kategooria"], $_POST["hind"]);
    header("Location: adminmenuu.php");
    exit();
}
$roogadeNimekiri = kysiMenuu($sorttulp, $otsisona);
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
<h4>Menüü</h4>
<br>

<div class="broncontainer">
    <div class="flexcontainer">
        <div id="tabel">
            <form action="adminmenuu.php">
                <label for="otsisona">Otsing:</label>
                <input type="text" name="otsisona" id="otsisona" placeholder="Sisesta otsingusõna">
            </form>
            <br>
            <h4>Roogide loetelu</h4>
            <table id="menuulist">
                <tr>
                    <td>Haldus</td>
                    <td><a href="adminmenuu.php?sort=nimetus">Nimetus</a></td>
                    <td><a href="adminmenuu.php?sort=kirjeldus">Kirjeldus</a></td>
                    <td><a href="adminmenuu.php?sort=kategooria">Kategooria</a></td>
                    <td><a href="adminmenuu.php?sort=hind">Hind</a></td>
                </tr>

                <?php foreach ($roogadeNimekiri as $amenuu): ?>
                    <?php if (isset($_GET["muutmisid"]) && intval($_GET["muutmisid"]) == $amenuu->id && isAdmin()): ?>
                        <tr>
                            <td colspan="5">
                                <form action="adminmenuu.php" method="post" >
                                    <input type="hidden" name="muudetudid" value="<?= $amenuu->id ?>">
                                    <input type="text" name="nimetus" value="<?= htmlspecialchars($amenuu->nimetus)?>">
                                    <input type="text" name="kirjeldus" value="<?= $amenuu->kirjeldus ?>">
                                    <br>
                                    <input type="text" name="kategooria" value="<?= $amenuu->kategooria ?>">
                                    <input type="text" name="hind" value="<?= $amenuu->hind ?>">
                                    <br>
                                    <input type="submit" name="muutmine" value="Muuda">
                                    <input type="submit" name="katkestus" value="Katkesta" onclick="window.location='adminmenuu.php'; return false;">
                                </form>
                            </td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td>
                                <?php if (isAdmin()): ?>
                                    <a href="adminmenuu.php?kustutusid=<?= $amenuu->id ?>" onclick="return confirm('Kustutada broneering?')">x</a>
                                    <a href="adminmenuu.php?muutmisid=<?= $amenuu->id ?>">m</a>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($amenuu->nimetus) ?></td>
                            <td><?= $amenuu->kirjeldus ?></td>
                            <td><?= $amenuu->kategooria ?></td>
                            <td><?= $amenuu->hind ?></td>
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
            <h4>Lisa uus roog</h4>
            <form action="adminmenuu.php" method="post">
                <table id="lisamenuu">
                    <?php if (isAdmin()): ?>
                        <tr>
                            <td><label for="nimetus">Nimetus:</label></td>
                            <td><input type="text" name="nimetus" required></td>
                        </tr>
                        <tr>
                            <td><label for="kirjeldus">Kirjeldus:</label></td>
                            <td><input type="text" name="kirjeldus" required></td>
                        </tr>
                        <tr>
                            <td><label for="kategooria">Kategooria:</label></td>
                            <td><input type="text" name="kategooria" required></td>
                        </tr>
                        <tr>
                            <td><label for="hind">Hind:</label></td>
                            <td><input type="text" name="hind" required></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td></td>
                        <td><input type="submit" name="lisa" id="lisa" value="Lisa"></td>
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