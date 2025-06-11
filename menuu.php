<?php
require("conf.php");
session_start();
global $yhendus;
$kask = $yhendus->prepare("SELECT kategooria, nimetus, hind FROM Menuu ORDER BY kategooria, nimetus");
$kask->execute();
$kask->bind_result($kategooria, $nimetus, $hind);
$menyy = [];
while ($kask->fetch()) {
    $menyy[$kategooria][] = ['nimetus' => $nimetus, 'hind' => $hind];
}
$kask->close();


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
                        <a class="active" href="menuu.php">Menüü</a>
                    </li>
                    <li>
                        <a href="login.php">Login</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
<br>
<div class="container">
    <h3>Menüü</h3>
    <form action="" method="post">
        <?php foreach ($menyy as $kategooria => $road): ?>
            <section id="menuutooded">
                <br>
                <h4><?= htmlspecialchars($kategooria) ?></h4>
                <ul>
                    <?php foreach ($road as $roog): ?>
                        <li>
                            <?= htmlspecialchars($roog['nimetus']) ?> - <?= number_format($roog['hind'], 2) ?> €
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
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