<?php
require('conf.php');
// Получение списка бронирований
function kysiBroneering($sorttulp="kuupaev", $otsisona=''){
    global $yhendus;
    $lubatudtulbad = array("nimi", "e_post", "laud", "kuupaev", "kellaaeg", "inimeste_arv");
    if(!in_array($sorttulp, $lubatudtulbad)){
        return "lubamatu tulp";
    }
    $otsisona=addslashes(stripslashes($otsisona));
    // 📋 Получение списка бронирований с учётом роли и сортировки
    $lubatudtulbad = array("nimi", "e_post", "laud", "kuupaev", "kellaaeg", "inimeste_arv");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "kuupaev"; // защита от SQL-инъекций
    }

    if (isAdmin()) {
        // Админ видит все
        $kask = $yhendus->prepare("
        SELECT b.broneering_id, k.nimi, k.e_post, k.kasutajanimi, b.laud, b.kuupaev, b.kellaaeg, b.inimeste_arv
        FROM Klient k, Broneering b
        WHERE b.klient_id=k.klient_id
        AND (nimi LIKE '%$otsisona%' OR kasutajanimi LIKE '%$otsisona%' OR e_post LIKE '%$otsisona%' OR laud LIKE '%$otsisona%' OR kuupaev LIKE '%$otsisona%' OR kellaaeg LIKE '%$otsisona%')
        ORDER BY $sorttulp
    ");
    } else {
        // Клиент видит только свои
        $kask = $yhendus->prepare("
        SELECT b.broneering_id, k.nimi, k.e_post, k.kasutajanimi, b.laud, b.kuupaev, b.kellaaeg, b.inimeste_arv
        FROM Broneering b
        JOIN Klient k ON b.klient_id = k.klient_id
        WHERE k.kasutajanimi = ?
        AND (nimi LIKE '%$otsisona%' OR kasutajanimi LIKE '%$otsisona%' OR e_post LIKE '%$otsisona%' OR laud LIKE '%$otsisona%' OR kuupaev LIKE '%$otsisona%' OR kellaaeg LIKE '%$otsisona%')
        ORDER BY $sorttulp
    ");
        $kask->bind_param("s", $_SESSION['klient']);
    }
    $kask->bind_result($id, $nimi, $e_post, $kasutajanimi, $laud, $kuupaev, $kellaaeg, $inimeste_arv);
    $kask->execute();

    $hoidla=array();
    while($kask->fetch()){
        $bron = new stdClass();
        $bron->broneering_id = $id;
        $bron->nimi = htmlspecialchars($nimi);
        $bron->kasutajanimi = htmlspecialchars($kasutajanimi);
        $bron->e_post = htmlspecialchars($e_post);
        $bron->laud = $laud;
        $bron->kuupaev = $kuupaev;
        $bron->kellaaeg = $kellaaeg;
        $bron->inimeste_arv = $inimeste_arv;
        array_push($hoidla, $bron);
    }
    return $hoidla;
}
function muudaBroneering($id, $laud, $kuupaev, $kellaaeg, $inimeste_arv) {
    global $yhendus;

    $kask = $yhendus->prepare("
        UPDATE Broneering
        SET laud = ?, kuupaev = ?, kellaaeg = ?, inimeste_arv = ?
        WHERE broneering_id = ?
    ");
    $kask->bind_param("issii", $laud, $kuupaev, $kellaaeg, $inimeste_arv, $id);
    $kask->execute();
}
function isAdmin() {
    return !empty($_SESSION['admin']);
}
function isKlient() {
    return !empty($_SESSION['klient']) && empty($_SESSION['admin']);
}
function kustutaBroneering($id){
    global $yhendus;
    $kask=$yhendus->prepare("DELETE FROM Broneering WHERE broneering_id=?");
    $kask->bind_param("i", $id);
    $kask->execute();
}
function kysiMenuu($sorttulp="kategooria", $otsisona='')
{
    global $yhendus;
    $lubatudtulbad = array("nimetus", "kirjeldus", "kategooria", "hind");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        return "lubamatu tulp";
    }
    $otsisona = addslashes(stripslashes($otsisona));
    $lubatudtulbad = array("nimetus", "kirjeldus", "kategooria", "hind");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "kategooria";
    }
    if (isAdmin()) {
        // Админ видит все
        $kask = $yhendus->prepare("
        SELECT id, nimetus, kirjeldus, kategooria, hind
        FROM Menuu
        WHERE (nimetus LIKE '%$otsisona%' OR kirjeldus LIKE '%$otsisona%' OR kategooria LIKE '%$otsisona%' OR hind LIKE '%$otsisona%')
        ORDER BY $sorttulp
    ");
        $kask->bind_result($id, $nimetus, $kirjeldus, $kategooria, $hind);
        $kask->execute();

        $hoidla=array();
        while($kask->fetch()){
            $amenuu = new stdClass();
            $amenuu->id = $id;
            $amenuu->nimetus = htmlspecialchars($nimetus);
            $amenuu->kirjeldus = htmlspecialchars($kirjeldus);
            $amenuu->kategooria = htmlspecialchars($kategooria);
            $amenuu->hind = $hind;
            array_push($hoidla, $amenuu);
        }
        return $hoidla;
    }
}
function kustutaRoog($id){
    global $yhendus;
    $kask=$yhendus->prepare("DELETE FROM Menuu WHERE id=?");
    $kask->bind_param("i", $id);
    $kask->execute();
}
function muudaRoog($id, $nimetus, $kirjeldus, $kategooria, $hind) {
    global $yhendus;

    $kask = $yhendus->prepare("
        UPDATE Menuu
        SET nimetus = ?, kirjeldus = ?, kategooria = ?, hind = ?
        WHERE id = ?
    ");
    $kask->bind_param("sssdi", $nimetus, $kirjeldus, $kategooria, $hind, $id);
    $kask->execute();
}
function kysiKlient($sorttulp="kategooria", $otsisona='')
{
    global $yhendus;
    $lubatudtulbad = array("nimi", "e_post", "telefon", "kasutajanimi", "parool", "onadmin");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        return "lubamatu tulp";
    }
    $otsisona = addslashes(stripslashes($otsisona));
    $lubatudtulbad = array("nimi", "e_post", "telefon", "kasutajanimi", "parool", "onadmin");
    if (!in_array($sorttulp, $lubatudtulbad)) {
        $sorttulp = "nimi";
    }
    if (isAdmin()) {
        // Админ видит все
        $kask = $yhendus->prepare("
        SELECT klient_id, nimi, e_post, telefon, kasutajanimi, parool, onadmin
        FROM Klient
        WHERE (nimi LIKE '%$otsisona%' OR e_post LIKE '%$otsisona%' OR telefon LIKE '%$otsisona%' OR kasutajanimi LIKE '%$otsisona%' OR kasutajanimi)
        ORDER BY $sorttulp
    ");
        $kask->bind_result($id, $nimi, $e_post, $telefon, $kasutajanimi, $parool, $onadmin);
        $kask->execute();

        $hoidla=array();
        while($kask->fetch()){
            $kliendid = new stdClass();
            $kliendid->klient_id = $id;
            $kliendid->nimi = htmlspecialchars($nimi);
            $kliendid->e_post = htmlspecialchars($e_post);
            $kliendid->telefon = htmlspecialchars($telefon);
            $kliendid->kasutajanimi = $kasutajanimi;
            $kliendid->parool = $parool;
            $kliendid->onadmin = $onadmin;
            array_push($hoidla, $kliendid);
        }
        return $hoidla;
    }
}
function muudaKlient($id, $nimi, $e_post, $telefon, $kasutajanimi, $parool, $onadmin) {
    global $yhendus;

    $kask = $yhendus->prepare("
        UPDATE Klient
        SET nimi = ?, e_post = ?, telefon = ?, kasutajanimi = ?, parool = ?, onadmin = ? 
        WHERE klient_id = ?
    ");
    $kask->bind_param("sssssii", $nimi, $e_post, $telefon, $kasutajanimi, $parool, $onadmin, $id);
    $kask->execute();
}
?>