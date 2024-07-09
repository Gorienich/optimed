<?php
require_once 'connectDB.php';     // חיבור לדטא בייס

?>
<?php
session_start();                             // תחילת SESSION
// משתנים ל ADMIN
$adminEmail = "egordyai88@gmail.com";                            //  ADMIN mail
$adminPassword = password_hash('egor1029', PASSWORD_DEFAULT);   // admin password
$adminValid = false;                                             // ADMIN לא מזוה

$errorForm = "";     // הודעה LOGIN


//$pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");//
//$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//
function validateUser($userEmail, $password, $pdo) // בדיקת משתמש
{
    try {
		$pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // החנת מידע של משתמש
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // בדיקת משתמש וסיסמא
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();    // הודעת שגיע
    } finally {
        // Close the database connection
        $pdo = null;
    }

}
// בדיקת משתמש אם הוא מסאזיסט
function validMasseur($userEmail, $password, $pdo)
{
    try {
        // prepare masseur data
        $stmt = $pdo->prepare("SELECT * FROM masseurs WHERE email = :email");
        $stmt->bindParam(':email', $userEmail);
        $stmt->execute();
        $masseur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($masseur && password_verify($password, $masseur['password'])) {
            return $masseur;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the database connection
        $pdo = null;
    }

}
// בדיקת משתמש אם הוא ADMIN
function validAdmin($userEmail, $password, $adminPassword, $adminEmail)
{
    if ($userEmail === $adminEmail && password_verify($password, $adminPassword)) {
        $adminValid = true;    // ADMIN מזוהה
        return $adminValid;
    }
}
// בקשה לשרת 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset ($_POST['login-submit'])) {      // קבתל SUBMIT
        // נתונים מהטופס
        $userEmail = $_POST['email'];
        $password = $_POST['password'];
        // הגנת נתונים
        $userEmail = htmlspecialchars($userEmail);
        $password = htmlspecialchars($password);
        $userEmail = trim($userEmail);
        $password = trim($password);
        // בדיקת תקינות של המייל
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL) || $userEmail == "") {
            $errorForm = "Invalid email format";
        } else {    // להגדיר משתנים לפי הפונקציות
            $user = validateUser($userEmail, $password, $pdo);                        // קבל לקוח או NULL                 
            $masseur = validMasseur($userEmail, $password, $pdo);                     // קבלת מסאזיסט או NULL
            $admin = validAdmin($userEmail, $password, $adminPassword, $adminEmail);  // בדיקת אימוט ADMIN

            if ($user) { // כניסת לקוח
                session_regenerate_id(true);          // הגנת TOKEN
                session_set_cookie_params([           // הגדרת SESSION
                    'lifetime' => 3600,               // זמן ל SESSION
                    'path' => '/',                    // אישור מעבר
                    'domain' => '.optimed.co.il',     // כתובת דומיין
                    'secure' => true,                 // הגנה
                    'httponly' => true,               // HTTPS
                    'samesite' => 'Lax'               // PROTECTED
                ]);
                //  של SESSION להגדיר משתנים 
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['Email'] = $user['email'];
                // לעביר לקוח לאזור אישי
                header('Location: ..\user\userDashboard.php');
                exit();  // לא לבצאע קוד נוסף
            }
            if ($masseur) {   // כניסת מסאזיסט
                session_regenerate_id(true);
                session_set_cookie_params([
                    'lifetime' => 3600,
                    'path' => '/',
                    'domain' => '.optimed.co.il',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                $_SESSION['masseurID'] = $masseur['masseurID'];
                $_SESSION['Email'] = $masseur['email'];
                $_SESSION['masseurName'] = $masseur['fname'];
                // להביר מסאזיסט לאזור אישי שלו 
                header('Location: ..\admin\masseurDashboard.php');
                exit();
            }
            if ($admin == true) {     // כניסת ADMIN
                session_regenerate_id(true);
                session_set_cookie_params([
                    'lifetime' => 3600,
                    'path' => '/',
                    'domain' => '.optimed.co.il',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                $_SESSION['Email'] = $adminEmail;
                $_SESSION['password'] = 'egor1029';
                header('Location: ..\admin\adminDashboard.php');   // להביר לעמוד הדמיניסטרטיבי
                exit();
            } else {   // ניסיון התחברות פסול לחדש את העמוד
                session_destroy();
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            }

            


        }
    }

    if (isset($_POST['repasswordSubmit'])) {
        if (!isset($_POST['emailPopUp'])) {
            echo '<script>alert("uncorreccted email!");</script>';
            exit; // Stop further execution if email is not set
        }
        
        $userMail = $_POST['emailPopUp'];
        $userMail = htmlspecialchars(trim($userMail));
        
        // Form to reset password
        $repasswordForm = '<form method="post" action="process_reset_password.php"> <!-- Add action to the form -->
                                <input type="text" name="pass1" placeholder="Your new password" maxlength="16">
                                <input type="text" name="pass2" placeholder="Repeat new password" maxlength="16">
                                <input type="hidden" name="userEmail" value="' . $userMail . '"> <!-- Add hidden input to pass user email -->
                                <button type="submit" name="sendRepassword">Send</button>
                          </form>';
        
        try {
            $stmt = $pdo->prepare("SELECT email,password FROM masseurs WHERE email = :email UNION SELECT email FROM users WHERE email = :email"); // Corrected SQL query
            $stmt->bindParam(':email', $userMail);
            $stmt->execute();
            $stmt_exist = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$stmt_exist) {
                echo '<script>alert("uncorreccted email!");window.location.href = "login.php";</script>';
                exit;
              
            }
    
            $to = $userMail;
            $subject = "!!!REPASSWORD Optimed!!!";
            $message = $repasswordForm;
            echo $message;
            $headers = "From: egordyai88@gmail.com";
           // mail($to, $subject, $message, $headers); unpack after smtp
            
          //  echo '<script>alert("Password sented successfully! Check your email.");</script>';

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
}
// סגירת חיבור DB
$pdo = null;
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="..\user\userCSS\register&loginCSS.css"> <!--  src לקובץ CSS  -->
</head>

<body>
<div id="loginForm">
    <div class="conteiner"> <!-- נביגציה  -->
        <div><a rel="noopener" noreferrerwindow.opener href="..\user\landingPage.php">Optimed</a></div>
        <div><a rel="noopener" noreferrerwindow.opener href="register.php">Registration</a></div>
    </div>
        <h2>Login</h2>
        <p>
            <?php echo $errorForm; ?> <!-- הודעה משרת  -->
        </p>
        <!-- טופס כניסה לאזור האישי  -->
        <form method="post">
            <label for="logEmail"></label>
            <input type="email" id="logEmail" name="email" placeholder="Email" autocomplete="off" required>
            <label for="logpassword"></label>
            <input type="password" id="logpassword" name="password" placeholder="password" required>
            <button type="submit" name="login-submit">Login</button>
        </form>
        <button class="showPopup" id="showPopupButton">Forgot Password?</button> <!-- כפתור להחלפת סיסמא -->
    </div>
    <!--popup להחלפת סיסמא-->
    <div class="container-popUp" id="popUp">
            <h2>Enter you email</h2>

            <!-- טופס להחלפת סיסמא  -->
            <form  method="post" enctype="multipart/form-data">
            <div id="popUp-close">x</div>
                <label for="emailPopUp"></label>
                <input type="email"  name="emailPopUp" placeholder="mail" autocomplete="off" required>
                <button type="submit" name="repasswordSubmit">Send</button>
            </form>
       
    </div>
    <script src="js\login.js"></script>
</body>

</html>