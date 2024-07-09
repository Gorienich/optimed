<?php
require_once '..\include\connectDB.php';
session_start();

if (!isset($_SESSION['Email']) || !isset($_SESSION['password'])) {
    session_destroy();
    header("Location: ../include/login.php");
    exit();
}

$pass = $_SESSION['password'];
$mail = $_SESSION['Email'];
if($pass !== 'egor1029' || $mail !== "egordyai88@gmail.com"){
    session_destroy();
    header("Location: ../include/login.php");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$header = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>contoller</title>
  <link rel="stylesheet" href="CSS\admin.css">
</head>
<body>
    <nav class="nav">
      <form method="post"><button type="submit" name="newMasseur">New messuer</button></form>
      <form method="post"><button type="submit" name="allMasseurs">Messuers</button></form>
      <form method="post"><button type="submit" name="allClients">Clients</button></form>
      <form method="post"><button type="submit" name="backToOptimed">Optimed</button></form>
      <form method="post"><button type="submit" name="logoutButton">Out</button></form>   
    </nav>';

$footer = '</body></html>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    if (isset ($_POST['logoutButton'])) {
      session_destroy();
      header("Location: ../include/login.php");
      exit();
    }
  
    if (isset ($_POST['backToOptimed'])) {
      header("Location: ../user/landingPage.php");
      exit();
    }
  
    if (isset ($_POST['allClients'])) {
      generateAllClients($pdo, $header, $footer);
    }
  
    if (isset ($_POST['deleteClient'])) {
      deleteClient($pdo, $header, $footer);
    }
  
    if (isset ($_POST['clientPhoneSubmitSearch'])) {
      searchClientByPhone($pdo, $header, $footer);
    }
    if (isset ($_POST['clientNameSubmitSearch'])) {
      searchClientByName($pdo, $header, $footer);
    }
  
  
    if (isset ($_POST['allMasseurs'])) {
      generateAllMesseurs($pdo, $header, $footer);
    }
    if (isset ($_POST['deleteMasseur'])) {
      deleteMasseur($pdo, $header, $footer);
    }
  
    if (isset ($_POST['newMasseur'])) {
      newMasseur($header, $footer);
    }
    if (isset ($_POST['newMasseurSubmit'])) {
      createNewMasseur($pdo, $header, $footer);
    }
  }
  
  function generateAllClients($pdo, $header, $footer)
  {
    try {
      // Fetch all users
      $stmt_all_clients = $pdo->prepare("SELECT * FROM users");
  
  
      $stmt_all_clients->execute();
      $allClientsData = $stmt_all_clients->fetchAll(PDO::FETCH_ASSOC);
  
      // Check if any clients exist
      if (!$allClientsData) {
        echo $header . "<h2>No clients yet.</h2>" . $footer;
        return;
      }
  
      // Build the HTML table for displaying clients
      $clientsTable = '<main><form method="post" action="#" style="margin: 10px auto; width: 50%;">
              <label style="font-weight: bold;" for="clientPhone">Search client by phone:</label>
              <input style="margin: 5px; text-align: center; padding: 5px;"  name="clientPhone" placeholder="Enter phone number" pattern="[0-9]{10}" title="Phone number must be a 10-digit number" maxlength="10" required>
              <button  type="submit" name="clientPhoneSubmitSearch">Find</button>
          </form><br>';
  
      $clientsTable .= '<form method="post"  style="margin: 10px auto; width: 50%;">
              <label style="font-weight: bold;" for="clientName">Search client by name:</label>
              <input style="margin: 5px 12px; text-align: center; padding: 5px;" type="text"  name="clientName"  placeholder="Enter client name" required>
              <button type="submit" name="clientNameSubmitSearch">Find</button>
          </form><br>';
  
      $clientsTable .= '<table class="table" border="1">';
      $clientsTable .= '<tr class="tr"><th>Client name</th><th>Client Lname</th><th>Tell</th><th>Mail</th><th>select</th><th>del</th></tr>';
  
      foreach ($allClientsData as $client) {
        $clientsTable .= '<tr><form method="post">';
        $clientsTable .= '<td>' . $client['username'] . '</td>';
        $clientsTable .= '<td>' . $client['lname'] . '</td>';
        $clientsTable .= '<td>' . $client['tell'] . '</td>';
        $clientsTable .= '<td>' . $client['email'] . '</td>';
        $clientsTable .= '<td><input type="checkbox" name="userID[]" value="' . $client['userID'] . '"></td>';
        $clientsTable .= '<td><button type="submit" name="deleteClient">Del</button></td>';
        $clientsTable .= '</tr>';
      }
  
      $clientsTable .= '</table></main>';
  
      echo $header . $clientsTable . $footer;
        exit;
    } catch (PDOException $e) {
      echo json_encode(['success' => false, 'message' => 'Error fetching clients data: ' . $e->getMessage()]);
    }
  }
  function deleteClient($pdo, $header, $footer)
  {
    try {
      if (isset ($_POST['userID'])) {
        $userID = $_POST['userID'];
  
        // If only one checkbox is checked, proceed with deletion
        if (is_array($userID) && count($userID) == 1) {
          $userID = reset($userID); // Get the first element of the array
          // Delete the user from the meetings table
          $stmt_delete_meetings = $pdo->prepare("DELETE FROM meetings WHERE userID = :userID");
          $stmt_delete_meetings->bindParam(':userID', $userID, PDO::PARAM_INT);
          $stmt_delete_meetings->execute();
  
          // Delete the user from the users table
          $stmt_delete_users = $pdo->prepare("DELETE FROM users WHERE userID = :userID");
          $stmt_delete_users->bindParam(':userID', $userID, PDO::PARAM_INT);
          $stmt_delete_users->execute();
  
          generateAllClients($pdo, $header, $footer);
                exit;
        } else {
          // If more than one checkbox is checked, alert the user
          echo  "<script>alert('Please select only one client to delete.')</script>";
        }
      } else {
        echo $header . "<main><h1>User not Found!</h1></main>" . $footer;
            exit;
      }
    } catch (PDOException $e) {
      header('location: ..\include\errorPage.php');
    }
  }
  
  
  function searchClientByPhone($pdo, $header, $footer)
  {
    try {
      $phoneNumber = $_POST['clientPhone'];
      // Prepare the SQL statement to search for clients by phone number
      $stmt_search_clients = $pdo->prepare("SELECT * FROM users WHERE tell = :phoneNumber");
      $stmt_search_clients->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
      $stmt_search_clients->execute();
      $clientsData = $stmt_search_clients->fetchAll(PDO::FETCH_ASSOC);
  
      if (!$clientsData) {
        echo $header . "<main><h2>No clients found with the phone number: $phoneNumber</h2></main>" . $footer;
        exit;
      }
  
      // Display the search results in a table
      $clientsTable = '<main><table border="1">';
      $clientsTable .= '<tr class="tr"><th>Client name</th><th>Client Lname</th><th>Tell</th><th>Mail</th><th>select</th><th>del</th></tr>';
  
      foreach ($clientsData as $client) {
        $clientsTable .= '<tr ><form method="post">';
        $clientsTable .= '<td>' . $client['username'] . '</td>';
        $clientsTable .= '<td>' . $client['lname'] . '</td>';
        $clientsTable .= '<td>' . $client['tell'] . '</td>';
        $clientsTable .= '<td>' . $client['email'] . '</td>';
        $clientsTable .= '<td><input type="checkbox" name="userID[]" value="' . $client['userID'] . '"></td>';
        $clientsTable .= '<td><button type="submit" name="deleteClient">Del</button></td>';
        $clientsTable .= '</tr>';
      }
  
      $clientsTable .= '</table></main>';
  
      echo $header . $clientsTable . $footer;
        exit;
    } catch (PDOException $e) {
      header('location: ..\include\errorPage.php');
    }
  
  }
  
  function searchClientByName($pdo, $header, $footer)
  {
    try {
      if (isset ($_POST['clientNameSubmitSearch'])) {
        $name = $_POST['clientName'];
        // Prepare the SQL statement to search for clients by name
        $stmt_search_clients = $pdo->prepare("SELECT * FROM users WHERE username = :name");
        $stmt_search_clients->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt_search_clients->execute();
        $clientsData = $stmt_search_clients->fetchAll(PDO::FETCH_ASSOC);
  
        if (!$clientsData) {
          echo $header . "<main><h2>No clients found with this name: $name</h2></main>" . $footer;
          exit;
        }
  
        // Display the search results in a table
        $clientsTable = '<main><table border="1">';
        $clientsTable .= '<tr class="tr"><th>Client name</th><th>Client Lname</th><th>Tell</th><th>Mail</th><th>select</th><th>del</th></tr>';
  
        foreach ($clientsData as $client) {
          $clientsTable .= '<tr><form method="post">';
          $clientsTable .= '<td>' . $client['username'] . '</td>';
          $clientsTable .= '<td>' . $client['lname'] . '</td>';
          $clientsTable .= '<td>' . $client['tell'] . '</td>';
          $clientsTable .= '<td>' . $client['email'] . '</td>';
          $clientsTable .= '<td><input type="checkbox" name="userID[]" value="' . $client['userID'] . '"></td>';
          $clientsTable .= '<td><button type="submit" name="deleteClient">Del</button></td>';
          $clientsTable .= '</tr>';
        }
  
        $clientsTable .= '</table></main>';
  
        echo $header . $clientsTable . $footer;
            exit;
      }
    } catch (PDOException $e) {
      header('location: ..\include\errorPage.php');
    }
  }
  
  function generateAllMesseurs($pdo, $header, $footer)
  {
    try {
      // Fetch all masseurs
      $stmt_all_masseurs = $pdo->prepare("SELECT * FROM masseurs");
      $stmt_all_masseurs->execute();
      $allMasseurData = $stmt_all_masseurs->fetchAll(PDO::FETCH_ASSOC);
  
      // Check if any masseurs exist
      if (!$allMasseurData) {
        echo $header . "<main><h2>No masseurs yet</h2></main>" . $footer;
        exit;
      }
  
      $masseursTable = '<main><table border="1">';
      $masseursTable .= '<tr class="tr"><th>Masseur name</th><th>Masseur Lname</th><th>Tell</th><th>Mail</th><th>select</th><th>del</th></tr>';
  
      foreach ($allMasseurData as $masseur) {
        $masseursTable .= '<tr><form method="post">';
        $masseursTable .= '<td>' . $masseur['fname'] . '</td>';
        $masseursTable .= '<td>' . $masseur['lname'] . '</td>';
        $masseursTable .= '<td>' . $masseur['tell'] . '</td>';
        $masseursTable .= '<td>' . $masseur['email'] . '</td>';
        $masseursTable .= '<td><input type="checkbox" name="masseurID" value="' . $masseur['masseurID'] . '"></td>';
        $masseursTable .= '<td><button type="submit" name="deleteMasseur">Delete</button></td>';
        $masseursTable .= '</form></tr>';
      }
  
      $masseursTable .= '</table></main>';
  
      echo $header . $masseursTable . $footer;
        exit;
    } catch (PDOException $e) {
      header('location: ..\include\errorPage.php');
    }
  }
  function deleteMasseur($pdo, $header, $footer)
  {
    try {
      if (isset ($_POST['masseurID'])) {
        $masseurID = $_POST['masseurID'];
        // Delete the masseur from the meetings table
        $stmt_delete_meetings = $pdo->prepare("DELETE FROM meetings WHERE masseurID = :masseurID");
        $stmt_delete_meetings->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
        $stmt_delete_meetings->execute();
  
        // Delete the masseur from the users table
        $stmt_delete_users = $pdo->prepare("DELETE FROM masseurs WHERE masseurID = :masseurID");
        $stmt_delete_users->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
        $stmt_delete_users->execute();
        generateAllMesseurs($pdo, $header, $footer);
  
      } else {
        generateAllMesseurs($pdo, $header, $footer);
      }
    } catch (PDOException $e) {
      header('location: ..\include\errorPage.php');
    }
  }
  
  function newMasseur( $header, $footer)
  {
    // Form for creating a new masseur
    $form = '<main><p>Create New Masseur<p>
      <form class="MasseurRegistrationForm parentMain" method="post">
      <label for="masseurID"></label>
      <input id="masseurID" placeholder="Masseur ID" type="text" name="masseurID" autocomplete="on" pattern="[0-9]{10}" title="Phone number must be a 10-digit number" maxlength="10" required>
      <label for="messeurfname"></label>
      <input id="messeurfname" placeholder="Masseur name" type="text" name="messeurName" autocomplete="on" maxlength="50" required>
      <label for="messeurlname"></label>
      <input id="messeurlname" placeholder="Masseur last name" type="text" name="messeurLname" autocomplete="on" maxlength="50" required>
      <label for="messeurEmail"></label>
      <input id="messeurEmail" placeholder="Masseur email" type="email" name="messeurEmail" autocomplete="on" required>
      <label for="password1"></label>
      <input id="password1" placeholder="Masseur password" type="password" name="messeurPass1" autocomplete="on" minlength="8" maxlength="16" required>
      <label for="password2"></label>
      <input id="password2" placeholder="Repeat password" type="password" name="messeurPass2" autocomplete="on" minlength="8" maxlength="16" required>
      <label for="messeurtel"></label>
      <input id="messeurtel" placeholder="Masseur phone" type="tel" name="messeurTell" autocomplete="on" pattern="[0-9]{10}" title="Phone number must be a 10-digit number" maxlength="10" required>
      <button style="width: 40%; margin: 15px 30%;" id="messeurSubmit" type="submit" name="newMasseurSubmit">Create</button>
      <button style="width: 40%; margin: 5px 30%;" type="reset">Reset</button>
  </form>
    </main>';
    echo $header . $form . $footer;
    exit;
  }
  
  function createNewMasseur($pdo, $header, $footer)
  {
      $mID = htmlspecialchars(trim($_POST['masseurID']));
      $messeurFname = htmlspecialchars(trim($_POST['messeurName'])); // Fixed variable name
      $messeurLname = htmlspecialchars(trim($_POST['messeurLname'])); // Fixed variable name
      $messeurEmail = htmlspecialchars(trim($_POST['messeurEmail']));
      $messeurPass1 = htmlspecialchars(trim($_POST['messeurPass1']));
      $messeurPass2 = htmlspecialchars(trim($_POST['messeurPass2']));
      $messeurTell = htmlspecialchars(trim($_POST['messeurTell']));
  
      if (
          empty($messeurEmail) || empty($messeurFname) || empty($messeurLname) ||
          empty($messeurPass1) || empty($messeurPass2) || empty($messeurTell)
      ) {
          echo $header . "<main><h1>All fields are required!</h1></main>" . $footer;
          exit;
      }
  
      if (!filter_var($messeurEmail, FILTER_VALIDATE_EMAIL)) {
          echo $header . "<main><h1>Invalid email format!</h1></main>" . $footer;
          exit;
      }
  
      if ($messeurPass1 !== $messeurPass2) {
          echo $header . "<main><h1>Passwords do not match!</h1></main>" . $footer;
          exit;
      }
  
      $hashed_password = password_hash($messeurPass1, PASSWORD_DEFAULT);
      try {
          // Check if the email or phone number already exists
          $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM masseurs WHERE email = :email");
          $stmt_check_email->bindParam(':email', $messeurEmail);
          $stmt_check_email->execute();
          $email_exists = $stmt_check_email->fetchColumn();
  
          $stmt_check_tel = $pdo->prepare("SELECT COUNT(*) FROM masseurs WHERE tell = :tell");
          $stmt_check_tel->bindParam(':tell', $messeurTell);
          $stmt_check_tel->execute();
          $tel_exists = $stmt_check_tel->fetchColumn(); // Fixed variable name
  
          if ($email_exists || $tel_exists ||  $messeurEmail === "egordyai88@gmail.com") {
              echo $header . "<main><h1>Email or phone number already exists!</h1></main>" . $footer;
              exit;
          } else {
              // Prepare and execute the SQL query to insert data into the users table
              $stmt = $pdo->prepare("INSERT INTO masseurs (masseurID, fname, lname, email, tell, password) 
                    VALUES (:masseurID, :fname, :lname, :email, :tell, :password)");
  
              $stmt->bindParam(':masseurID', $mID);
              $stmt->bindParam(':fname', $messeurFname);
              $stmt->bindParam(':lname', $messeurLname);
              $stmt->bindParam(':email', $messeurEmail);
              $stmt->bindParam(':tell', $messeurTell);
              $stmt->bindParam(':password', $hashed_password);
              $stmt->execute();
              echo $header . "<main><h1>Created successfully!</h1></main>" . $footer;
              exit;
          }
      } catch (PDOException $e) {
          header('location: ..\include\errorPage.php');
      }
  }
  


echo $header . "<main><h1>Welcome Admin</h1><h2>System is Ready...</h2></main>" . $footer;

?>