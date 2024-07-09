<?php
require_once '..\include\connectDB.php'; // Database connection
?>

<?php
$errorForm = ""; // Error message for the user

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if it's a POST request
    if (isset($_POST['newUser'])) { // Check if the 'newUser' button is clicked
        // Retrieve user input
        $userFname = $_POST['UserName'];
        $userLname = $_POST['UserLastName'];
        $userEmail = $_POST['UserEmail'];
        $userPass1 = $_POST['pass1'];
        $userPass2 = $_POST['pass2'];
        $userTell = $_POST['UserTell'];

        // Sanitize user input
        $userFname = htmlspecialchars(trim($userFname));
        $userLname = htmlspecialchars(trim($userLname));
        $userEmail = htmlspecialchars(trim($userEmail));
        $userPass1 = htmlspecialchars(trim($userPass1));
        $userPass2 = htmlspecialchars(trim($userPass2));
        $userTell = htmlspecialchars(trim($userTell));

        // Validate email format
        if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL) || $userEmail === "" || $userTell === "") {
            $errorForm = "Invalid email format or phone number is empty";
        } else {
            // Hash the password (for security)
            $hashed_password = password_hash($userPass1, PASSWORD_DEFAULT);

            try {
                // Create a new PDO instance
                $pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Check if email exists in the users table
                $stmt_check_email = $pdo->prepare("SELECT email,tell FROM users WHERE email = :email OR tell = :tell");
                $stmt_check_email->bindParam(':email', $userEmail);
                $stmt_check_email->bindParam(':tell', $userTell);
                $stmt_check_email->execute();
                $exists_user = $stmt_check_email->fetchAll();

                // Check if email exists in the masseurs table
                $stmt_check_account = $pdo->prepare("SELECT * FROM masseurs WHERE email = :email OR tell = :tell");
                $stmt_check_account->bindParam(':email', $userEmail);
                $stmt_check_account->bindParam(':tell', $userTell);
                $stmt_check_account->execute();
                $account_exists_masseur = $stmt_check_account->fetchAll(PDO::FETCH_ASSOC);

                if (count($exists_user) > 0 || count($account_exists_masseur) > 0) {
                    $errorForm = "Email or phone number is already in use";
                } else { // Perform registration
                    // Check if trying to register with admin email
                    if ($userEmail == "egordyai88@gmail.com" || $userTell == "egor1029") {
                        $errorForm = "Email or phone number is already in use";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO users (username, lname, email, tell, password)
                                           VALUES (:username, :lname, :email, :tell, :password)");

                        $stmt->bindParam(':username', $userFname);
                        $stmt->bindParam(':lname', $userLname);
                        $stmt->bindParam(':email', $userEmail);
                        $stmt->bindParam(':tell', $userTell);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->execute();

                        // Redirect user to login page
                        header('Location: login.php');
                        exit(); // Terminate the script after redirection
                    }
                }
            } catch (PDOException $e) {
                $errorForm = "Error: " . $e->getMessage();
            } finally {
                $pdo = null; // Close the database connection
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>registration</title>   
     <link rel="stylesheet" href="..\user\userCSS\register&loginCSS.css">     <!-- src to css file  -->
</head>

<body>
     <div class="conteiner">
          <div><a rel="noopener" noreferrerwindow.opener href="..\user\landingPage.php">Optimed</a></div>  <!-- redirect to landing page -->
          <div><a rel="noopener" noreferrerwindow.opener href="login.php">login</a></div>       <!-- redirect to login page -->
     </div>
     <h2>Creat profile</h2>
     <div class="error">
          <p><?php echo $errorForm; ?></p>     <!-- server error response  -->
     </div>
     <form id="registrationForm"  method="post" enctype="multipart/form-data" required>
          <input id="fname" placeholder="Enter your name" type="text" name="UserName" autocomplete="on" required>
          <input id="lname" placeholder="entar your last name" type="text" name="UserLastName" autocomplete="on"  required>
          <input id="email" placeholder="enter e-mail" type="email" name="UserEmail" autocomplete="off" required>
          <input id="pass1" placeholder="create your password  '8-20 chars'" type="password" name="pass1" value=""  autocomplete="off" required>
          <input id="pass2" placeholder="repeat your password  '8-20 chars'" type="password" name="pass2" value="" autocomplete="off" required>
          <input id="userTell" placeholder="Enter user tell" type="text" name="UserTell" pattern="[0-9]{10}" title="phone numberhave to be a 10-digit number" maxlength="10" required>
          <button type="submit" name="newUser">Create</button>  
          <button type="reset">reset</button>
     </form>
     <script src="js\registration.js"></script>  <!-- crs to script file -->
</body>
</html>