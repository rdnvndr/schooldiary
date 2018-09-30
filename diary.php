<?php

$site         = "http://www.yourhost.com/diary.php";
$school        = "1234567890123";
$yourlogin    = "yourlogin";
$yourpassword = "yourpassword";
$email        = "email@example.ru";

//! Получение дневника за неделю
function diary($date, $url,$login,$pass, $dst){
    $ch = curl_init();

    // если соединяемся с https
    if(strtolower((substr($url,0,5))=='https')) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    // откуда пришли на эту страницу
    curl_setopt($ch, CURLOPT_REFERER, $url);
    // cURL будет выводить подробные сообщения о всех производимых действиях
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,"login=".$login."&password=".$pass);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36");
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //сохранять полученные COOKIE в файл
    curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/cookie.txt');
    $result=curl_exec($ch);
    curl_close($ch);

    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    if(strpos($result,"https://school.mosreg.ru:443/?utm_source=school.mosreg&utm_medium=uslugi&utm_campaign=login")===false) {
        mail($GLOBALS['email'],"Mosreg авторизация: [FAIL]","Mosreg авторизация: [FAIL]",$headers);
        return 'Fail';
    } else {
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        $result = Read("https://schools.school.mosreg.ru/marks.aspx?school=" . $GLOBALS['school'] . "&tab=week&year=". $year . "&month=" . $month . "&day=" . $day);

        // просмотр следующей недели на веб странице
        $date->modify('+7 day');
        $year  = $date->format('Y');
        $month = $date->format('m');
        $day   = $date->format('d');
        $next  = $GLOBALS['site'] . "?year=". $year . "&month=" . $month . "&day=" . $day . "&dst=web";

        // просмотр предыдущей недели на веб странице
        $date->modify('-14 day');
        $year  = $date->format('Y');
        $month = $date->format('m');
        $day   = $date->format('d');
        $prev  = $GLOBALS['site'] . "?year=". $year . "&month=" . $month . "&day=" . $day . "&dst=web";

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($result);
        $node = $doc->getElementById('diarydays');

        $newdoc = new DOMDocument;
        $newdoc->formatOutput = true;
        $newdoc->loadHTMLFile($_SERVER['DOCUMENT_ROOT'].'/tmpl.html');
        $node = $newdoc->importNode($node, true);
        $newdoc->getElementById('pageAuth')->appendChild($node);
        $link = $newdoc->getElementById('prev');
        $link->setAttribute('href', $prev);
        $link = $newdoc->getElementById('next');
        $link->setAttribute('href', $next);
        $result = $newdoc->saveHTML();
        $result = str_replace("width:17%","width:1%", $result);
        $result = str_replace("width:7%","width:1%", $result);
        $result = str_replace("width:41%","width:63%", $result);
        if ($dst == "web")
            return $result;
        else {
            mail($GLOBALS['email'],"Электронный дневник: Успеваемость",$result,$headers);
            return 'OK';
        }
    }
}

// чтение страницы после авторизации
function Read($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // откуда пришли на эту страницу
    curl_setopt($ch, CURLOPT_REFERER, $url);
    //запрещаем делать запрос с помощью POST и соответственно разрешаем с помощью GET
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    //отсылаем серверу COOKIE полученные от него при авторизации
    curl_setopt($ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'].'/cookie.txt');
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
}

$day   = isset($_GET['day'])   ? $_GET['day']   : "";
$month = isset($_GET['month']) ? $_GET['month'] : "";
$year  = isset($_GET['year'])  ? $_GET['year']  : "";
$dst   = isset($_GET['dst'])   ? $_GET['dst']   : "";

if ($year && $month && $day)
    $date = new DateTime($year . "-" . $month . "-" . $day);
else
    $date = new DateTime();

echo diary($date, "https://login.school.mosreg.ru/user/login",$yourlogin,$yourpassword,$dst);

?>
