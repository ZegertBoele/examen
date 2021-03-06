<?php
// Begint een sessie en geeft de rank van de gebruiker terug
function Session()
{
    session_start();
    session_regenerate_id();
    if (isset($_SESSION['rank'])) {
        return $_SESSION['rank'];
    } else {
        return 0;
    }
}
// Begint een sessie en geeft het ID van een ingelogde gebruiker terug
function ID()
{
    session_start();
    session_regenerate_id();
    if (isset($_SESSION['ID'])) {
        return $_SESSION['ID'];
    } else {
        return 0;
    }
}
// Checkt of de gebruiker de ingegeven rank heeft, zo niet logt de gebruiker uit.
function CheckRank($rank)
{
    if (Session() >= $rank) {
        // Echoed de rank van de gebruiker, puur voor development.
        // echo "Rank: " . Session() . " ID:" . ID();
    } else {
        header("location:https://ex83504.ict-lab.nl/logout.php");
    }
}

// Niet alle items in de nav zijn voor alle gebruikers bedoelt, deze functie checkt de rank en bepaalt dan of het wel of niet getoont wordt.
function CheckRankNav($rank, $showItem)
{
    if (Session() >= $rank) {
        $showItem;
    }
}

// Haalt een aantal rare tekens uit een string, tegen SQL injecties
function SQLInjectionFormat($string)
{
    $formatted_string = preg_replace('~[\\\\/:*?"<>|]~', ' ', $string);
    return $formatted_string;
}

function ErrorCard($message){
    echo "<div class='card'><div class='card-body' style='width:90vw; margin-left:5vw;text-align:center;'>";
    echo $message . "<br>";
    echo "<a href='https://ex83504.ict-lab.nl/home/' class='btn btn-primary' role='button'>Ga Terug</a>";
    echo "</div></div>";
}

// Functie verbindt met de database.
function Conn()
{
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    $dsn         = "mysql:host=localhost;dbname=ex_83504";
    $DB_username = "ex83504";
    $DB_password = "Cy8o^n68";

    try {
        $conn = new PDO($dsn, $DB_username, $DB_password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $error = "Geen connectie mogelijk. Error: " . $e->getMessage();
        return $error;
    }
    return $conn;
}
// Deze functie voegt een gebruiker toe.
function AddUser($username, $password, $firstname, $lastname, $adress, $town, $phone, $email, $member)
{
    try {
        $stmt = Conn()->prepare("INSERT INTO users(ID, username, password, rank, firstname, lastname, adress, town, phone, email, member, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([null, SQLInjectionFormat($username), $password, 1, SQLInjectionFormat($firstname), SQLInjectionFormat($lastname), SQLInjectionFormat($adress), SQLInjectionFormat($town), SQLInjectionFormat($phone), $email, $member, null, null]);
        $adduser = true;
    } catch (PDOException $e) {

        $adduser =  ErrorCard("Nieuwe gebruiker niet toegevoegd. Error: " . $e->getMessage());
    }
    return $adduser;
}
// Selecteert alle items uit de times tabel, speciaal voor de admins
function SelectAllTimeAdmin()
{
    $stmt = Conn()->prepare("SELECT * FROM times ORDER BY date DESC");
    $stmt->execute();
    return $stmt;
}

// Selecteert alle items uit de times tabel
function SelectAllTime()
{
    $stmt = Conn()->prepare("SELECT * FROM times WHERE hidden=0 ORDER BY date DESC");
    $stmt->execute();
    return $stmt;
}

function SelectMyTime($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM user_on_time WHERE ID_user=?");
    $stmt->execute([$ID]);
    return $stmt;
}

function SelectFromMyTimeTimes($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM times WHERE ID=? ORDER BY date DESC");
    $stmt->execute([$ID]);
    return $stmt;
}
// Geeft het aantal vrije plekken terug
function AmountSpaceFree($amount_people_in)
{
    $answer = 100 - $amount_people_in;
    return $answer;
}

function CheckIfUserIsAlreadyRegistered()
{
    $result = Conn()->prepare("SELECT * FROM user_on_time WHERE ID = ?");
    $result->execute([ID()]);
    return $result->fetchAll();
}
// Registreert een reservering en telt er een bij op in de times tabel.
function Register($ID)
{
    try {
        if (empty(CheckIfUserIsAlreadyRegistered())) {
            if (CheckAmountOfReservations($ID) <= 99) {
                $stmt_insert = Conn()->prepare("INSERT INTO user_on_time(ID, ID_user, ID_time, cancelled, created_at, updated_at) VALUES (?,?,?,?,?,?)");
                $stmt_insert->execute([null, ID(), $ID, 0, null, null]);
                $stmt_increment_times = Conn()->prepare("UPDATE times SET amount_people_in = amount_people_in + 1 WHERE ID=?");
                $stmt_increment_times->execute([$ID]);
                $register = true;
            } else {
                $register = ErrorCard("Dit moment zit helaas al vol.");
            }
        } else {
            $register = ErrorCard("U bent al ingeschreven.");
        }
    } catch (PDOException $e) {
        if (strpos($e, "SQLSTATE[23000]") !== false) {
            $register = ErrorCard("U hebt al gereserveerd voor een tijdsblok.");
        } else {
            $register = ErrorCard("Reservering niet geslaagd. Error: " . $e->getMessage());
        }
    }
    return $register;
}

// functie die een gemaakte reservering ongedaan maakt
function UnRegister($amount, $ID, $ID_times)
{
    try {
        $stmt_insert = Conn()->prepare("DELETE FROM user_on_time WHERE ID=?");
        $stmt_insert->execute([$ID]);
        $amount = $amount - 1;
        $stmt_times = Conn()->prepare("UPDATE times SET amount_people_in=? WHERE ID=?");
        $stmt_times->execute([$amount, $ID_times]);
        $unregister = true;
    } catch (PDOException $e) {
        $unregister = ErrorCard("Reservering verwijderen niet geslaagd. Error: " . $e->getMessage());
    }
    return $unregister;
}

// functie die ervoor zorgt dat je items aan de functie tabel kan toevoegen
function AddTime($date, $starttime, $endtime, $amount_people_in, $hidden)
{
    try {
        $stmt = Conn()->prepare("INSERT INTO times(ID, date, starttime, endtime, amount_people_in, hidden, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([null, $date, $starttime, $endtime, $amount_people_in, $hidden, null, null]);
        var_dump($stmt);
        $addtime = true;
    } catch (PDOException $e) {
        $addtime = ErrorCard("Tijd niet toegevoegd. Error: " . $e->getMessage());
    }
    return $addtime;
}

// Functie die ervoor zorgt dat je de time tabel kan updaten
function UpdateTime($date, $starttime, $endtime, $amount_people_in, $hidden = 0, $ID)
{
    try {
        $stmt = Conn()->prepare("UPDATE times SET date=?,starttime=?,endtime=?,amount_people_in=?,hidden=? WHERE ID=?");
        $stmt->execute([$date, $starttime, $endtime, $amount_people_in, $hidden, $ID]);
        $updatetime = true;
    } catch (PDOException $e) {
        $updatetime = ErrorCard("Tijd niet gewijzigd. Error: " . $e->getMessage());
    }
    return $updatetime;
}

// Functie die ervoor zorgt dat er een times-row verwijderd wordt
function DeleteTime($ID)
{
    try {
        $stmt = Conn()->prepare("DELETE FROM times WHERE ID=?");
        $stmt->execute([$ID]);
        $deletetime = true;
    } catch (PDOException $e) {
        $deletetime = ErrorCard("Tijd niet verwijderd. Error: " . $e->getMessage());
    }
    return $deletetime;
}

function SelectUserIDsFromReservation($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM user_on_time WHERE ID_time=?");
    $stmt->execute([$ID]);
    return $stmt;
}

function GetUserData($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM users WHERE ID=?");
    $stmt->execute([$ID]);
    return $stmt;
}

// functie die alle data van een specifieke 'users'-row teruggeeft
function GetPersonalData()
{
    $stmt = Conn()->prepare("SELECT * FROM users WHERE ID=?");
    $stmt->execute([ID()]);
    $data = $stmt->fetch();
    return $data;
}

// Functie die alle data van een specifieke 'times' -row teruggeeft
function GetTimesData($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM times WHERE ID=?");
    $stmt->execute([$ID]);
    $data = $stmt->fetch();
    return $data;
}

// Functie die alleen het aantal reserveringen teruggeeft
function CheckAmountOfReservations($ID)
{
    $stmt = Conn()->prepare("SELECT * FROM times WHERE ID=?");
    $stmt->execute([$ID]);
    $data = $stmt->fetch();
    return $data['amount_people_in'];
}

// Functie maakt een text bestand aan waarin veel data van de gebruiker staat en de afspraak. Zodat het gedownload kan worden.
function CreateReserveFile($ID)
{
    $data = GetPersonalData();
    $times = GetTimesData($ID);
    $file = "reservering.txt";
    $txt = fopen($file, "w") or die("Unable to open file!");
    $content = "Reservering Schaatsbaan \n Naam: " . $data['firstname'] . " " . $data['lastname'] . "\n Woonplaats: " . $data['adress'] .  " " . $data['town'] . " \n Tijdstip: " . $times['date'] . " " . $times['starttime'] . " - " . $times['endtime'] . "\n Reserveringsnummer: " . $ID;
    fwrite($txt, $content);
    fclose($txt);

    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    header("Content-Type: text/plain");
    readfile($file);
}
