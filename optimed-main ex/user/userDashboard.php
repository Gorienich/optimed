<?php
require_once '..\include\connectDB.php';
session_start();

// Check if all session variables are set
if (!isset ($_SESSION['userID']) || !isset ($_SESSION['Email']) || !isset ($_SESSION['username'])) {
  session_destroy();
  header("Location: ../include/login.php");
  exit();
}
$userFname = $_SESSION['username'];
$userMail = $_SESSION['Email'];
$userID = $_SESSION['userID'];
$pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$header = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
    <link rel="stylesheet" href="userCSS\userCSS.css">
</head>
<body>
    <nav class="nav">
      <form class="navy" method="post"><button type="submit" name="newMeeting">New Meeting</button></form>
      <form class="navy" method="post"><button type="submit" name="allMeetings">My meetings</button></form>
      <form class="navy" method="post"><button type="submit" name="backToOptimed">Optimed</button></form>
      <form class="navy" method="post"><button type="submit" name="logoutButton">Out</button></form>
      <form class="navy" method="post"><button type="submit" name="deleteAccount">Delete Acount</button></form>
    </nav><main>';
$footer = '</main><script src="userJS.js"></script></body></html>';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (isset ($_POST['logoutButton'])) {
    session_destroy();
    header("Location: ../include/login.php");
    exit();
  }

  if (isset ($_POST['backToOptimed'])) {
    header("Location: ../user/landingPage.php");
    exit;
  }

  if (isset ($_POST['deleteAccount'])) {
    deleteAccount($header, $footer, $userID);
    exit;
  }

  if (isset ($_POST['accDelete'])) {
    removeAccount($userID, $pdo);
    exit;
  }

  if (isset ($_POST['allMeetings'])) {
    allMeetings($header, $footer);
  }

  if (isset ($_POST['meetingsStory'])) {
    userHistory($header, $footer, $userID, $pdo);
  }

  if (isset ($_POST['searchMeetingsDate'])) {
    searchMeetingDay($userID, $pdo, $header, $footer);
  }

  if (isset ($_POST['deleteMeeting'])) {
    deleteMeeting($pdo, $header, $footer, $userMail);
    exit;
  }
  if (isset ($_POST['newMeeting'])) {
    newMeetingForm($header, $footer, $pdo);
    exit;
  }

  if (isset ($_POST['showCalendar'])) {
    showCalendar($pdo, $currentMonth = null, $currentYear = null, $header, $footer);
  }
  if (isset ($_POST['getPrevMonth'])) {
    showCalendar($pdo, $_POST['prevMonth'], $_POST['prevYear'], $header, $footer);
  }
  if (isset ($_POST['getNextMonth'])) {
    showCalendar($pdo, $_POST['nextMonth'], $_POST['nextYear'], $header, $footer);
  }

  if (isset ($_POST['createNewMeeting'])) {
    newMeeting($pdo, $header, $footer, $userID, $userMail);
  }
}
function newMeeting($pdo, $header, $footer, $userID, $userMail)  // create new meeting function
{

  $day = $_POST['meetingDay'];
  $month = $_POST['meetingMonth'];
  $year = $_POST['meetingYear'];
  $meetingTime = $_POST['meetingsTime'];
  $masseurID = $_POST['masseurID'];


  $day = htmlspecialchars(trim($day));
  $month = htmlspecialchars(trim($month));
  $year = htmlspecialchars(trim($year));

  $meetingTime = htmlspecialchars(trim($meetingTime));
  $masseurID = htmlspecialchars(trim($masseurID));

  // Check if any of the date components are empty
  if (empty ($day) || empty ($month) || empty ($year) || empty ($meetingTime) || empty ($masseurID) || empty ($meetingTime)) {
    echo '<script>alert("Invalid data of meeting provided.");</script>';
    return;
  }

  $month = date('m', strtotime($month)); // convet month to diggit
  $meeting_date = $year . '-' . $month . '-' . $day;

  try {

    // Check if meeting exist in meetings
    $stmt_check_meeting = $pdo->prepare("SELECT * FROM meetings WHERE DATE_FORMAT(meeting_time, '%H:%i') = :meeting_time AND masseurID = :masseurID AND YEAR(meeting_date) = :year AND MONTH(meeting_date) = :month AND DAY(meeting_date) = :day");
    $stmt_check_meeting->bindParam(':meeting_time', $meetingTime);
    $stmt_check_meeting->bindParam(':masseurID', $masseurID);
    $stmt_check_meeting->bindParam(':year', $year);
    $stmt_check_meeting->bindParam(':month', $month);
    $stmt_check_meeting->bindParam(':day', $day);
    $stmt_check_meeting->execute();
    $meeting_exists = $stmt_check_meeting->fetch(PDO::FETCH_ASSOC);

    // response of check
    if ($meeting_exists) {
      echo '<script>alert("meeting exist for this time!check data.");</script>';
    } else {
      // valid parameters insert meeting in db
      $meeting_date = "$year-$month-$day";
      $stmt = $pdo->prepare("INSERT INTO meetings (masseurID, userID, meeting_date, meeting_time) 
      VALUES (:masseurID, :userID, STR_TO_DATE(:meeting_date, '%Y-%m-%d'), :meeting_time)");
      $stmt->bindParam(':masseurID', $masseurID);
      $stmt->bindParam(':userID', $userID);

      $stmt->bindParam(':meeting_date', $meeting_date);
      $stmt->bindParam(':meeting_time', $meetingTime);
      $stmt->execute();

      $to = $userMail;
      $subject = "Meeting canceled! on Optimed";
      $message = "Created new meeting for " . $meeting_date . " at " . $meetingTime . " has been canceled. If you need to contact us, you can visit our site or contact us by phone: 0502196936.";
      $headers = "From: egordyai88@gmail.com";
      //mail($to, $subject, $message, $headers);   open after config SMTP


      echo '<script>alert("created successfuly!");</script>';
      return;
    }
  } catch (PDOException $e) {
    echo $header . '<h1>Error occurred: ' . $e->getMessage() . '</h1>' . $footer;
  }
}

function deleteAccount($header, $footer, $userID)
{
  $form = '<form class="form" method="post">
           <h1 style="color: red;">by click the buuton you will delete your account from Optimed and cancel all your meeting if it has</h1>
           <input type="hidden" value="' . $userID . '" name="userID">
           <button class="btn" name="accDelete">delet account</button></form>';

  echo $header . $form . $footer;
  exit;
}

function removeAccount($userID, $pdo)
{
  try {
    if (isset ($_POST['userID'])) {
      $userID = $_POST['userID'];

      $stmt_delete_meetings = $pdo->prepare("DELETE FROM meetings WHERE userID = :userID");
      $stmt_delete_meetings->bindParam(':userID', $userID, PDO::PARAM_INT);
      $stmt_delete_meetings->execute();

      // Delete the user from the users table
      $stmt_delete_users = $pdo->prepare("DELETE FROM users WHERE userID = :userID");
      $stmt_delete_users->bindParam(':userID', $userID, PDO::PARAM_INT);
      $stmt_delete_users->execute();

      echo '<script>alert("account deleted!");</script>';
      session_destroy();
      header('location: landingPage.php');
      exit;
    } else {
      echo "<script>alert('bad request to delete.')</script>";
    }
  } catch (PDOException $e) {
    header('location: ..\include\errorPage.php');
  }
}
function allMeetings($header, $footer)
{
  $table = '<form method="post">
              <input style="margin: 100px 40% 20px 40%; width: 20%;" type="date" name="selectedDate">
              <button style="margin: 20px 40%; width: 20%;" type="submit" name="searchMeetingsDate">Find</button>
            </form>
            <form method="post">
              <button style="margin: 50px 40%; width: 20%;" type="submit" name="meetingsStory">All meetings</button>
            </form>';

  echo $header . $table . $footer;
  exit;
}

function newMeetingForm($header, $footer, $pdo)
{
  try {
    // Fetch masseurs' IDs, first names, and last names
    $stmt_masseursIDs = $pdo->prepare("SELECT masseurID, fname, lname FROM masseurs");
    $stmt_masseursIDs->execute();
    $masseursData = $stmt_masseursIDs->fetchAll(PDO::FETCH_ASSOC);

    // Build the select option for masseurs
    $selectOption = '<h1>Select a masseur</h1>';
    $selectOption .= '<form method="post">';
    $selectOption .= '<select name="selectedMasseur" style="margin: 20px 40% 20px 40%; width: 20%;">';
    foreach ($masseursData as $masseur) {
      $selectOption .= '<option value="' . $masseur['masseurID'] . '">' . $masseur['fname'] . ' ' . $masseur['lname'] . '</option>';
    }
    $selectOption .= '</select>';
    $selectOption .= '<button type="submit" name="showCalendar" style="margin: 20px 40%; width: 20%;">Show Calendar</button>';
    $selectOption .= '</form>';

    echo $header . $selectOption . $footer;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}




function searchMeetingDay($userID, $pdo, $header, $footer)
{
  if (!isset ($_POST['selectedDate'])) {
    echo '<script>alert("Incorrect date format");</script>';
    return;
  }

  $dateData = $_POST['selectedDate'];
  $dateData = htmlspecialchars(trim($dateData));
  // Convert date format from day.month.year to year-month-day
  $dateData = date('Y-m-d', strtotime($dateData));

  try {
    // Fetch all meetings of a day for the given masseur and order by meeting time
    $stmt_check_meetings = $pdo->prepare("SELECT meetings.meetingID, meetings.meeting_date, meetings.userID, masseurs.fname, masseurs.lname, masseurs.tell, masseurs.email, DATE_FORMAT(meetings.meeting_time, '%H:%i') AS meeting_time FROM meetings INNER JOIN masseurs ON meetings.masseurID = masseurs.masseurID WHERE meetings.userID = :userID AND meetings.meeting_date = :meeting_date ORDER BY meetings.meeting_time");
    $stmt_check_meetings->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt_check_meetings->bindParam(':meeting_date', $dateData, PDO::PARAM_STR);
    $stmt_check_meetings->execute();
    $meetingsDayData = $stmt_check_meetings->fetchAll(PDO::FETCH_ASSOC);

    // Check if any meetings exist for the given day
    if (!$meetingsDayData) {
      echo $header . "<h1>No meetings for choosen date</h1></main>" . $footer;
      exit;
    }

    // Build the HTML table for displaying meetings
    $allMeetings = '<form class="table"  method="post"><table border="1">';
    $allMeetings .= '<tr class="tr"><th>Masseur name</th><th>Tell</th><th>Mail</th><th>Time</th><th>date</th><th>Select</th><th>cancel</th></tr>';

    foreach ($meetingsDayData as $meeting) {
      $allMeetings .= '<tr>';
      $allMeetings .= '<td>' . $meeting['fname'] . " " . $meeting['lname'] . '</td>';
      $allMeetings .= '<td>' . $meeting['tell'] . '</td>';
      $allMeetings .= '<td>' . $meeting['email'] . '</td>';
      $allMeetings .= '<td>' . $meeting['meeting_time'] . '</td>';
      $allMeetings .= '<td>' . $meeting['meeting_date'] . '</td>';
      $allMeetings .= '<input type="hidden" name="date" value="' . $meeting['meeting_date'] . '">';
      $allMeetings .= '<input type="hidden" name="meetingTime" value="' . $meeting['meeting_time'] . '">';
      $allMeetings .= '<td><input type="checkbox" name="meetingID[]" value="' . $meeting['meetingID'] . '"></td>';
      $allMeetings .= '<td><input type="hidden" name="userID[]" value="' . $meeting['userID'] . '"><button type="submit" name="deleteMeeting">Del</button></td>';
      $allMeetings .= '</tr>';
    }

    $allMeetings .= '</table>';

    echo $header . $allMeetings . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<h1>Error occurred: ' . $e->getMessage() . '</h1>' . $footer;
    exit;
  }
}

function userHistory($header, $footer, $userID, $pdo)
{


  try {
    // Fetch all meetings of the user sorted by meeting date (biggest to smallest) and time
    $stmt_check_meetings = $pdo->prepare("SELECT meetings.meetingID, meetings.meeting_date, meetings.userID, masseurs.fname, masseurs.lname, masseurs.tell, masseurs.email, DATE_FORMAT(meetings.meeting_time, '%H:%i') AS meeting_time FROM meetings INNER JOIN masseurs ON meetings.masseurID = masseurs.masseurID WHERE meetings.userID = :userID ORDER BY meetings.meeting_date DESC, meetings.meeting_time");
    $stmt_check_meetings->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt_check_meetings->execute();
    $meetingsDayData = $stmt_check_meetings->fetchAll(PDO::FETCH_ASSOC);

    // Check if any meetings exist for the user
    if (!$meetingsDayData) {
      echo $header . "<h1>No meetings Yet...</h1>" . $footer;
      exit;
    }

    // Build the HTML table for displaying meetings
    $allMeetings = '<form class="table"  method="post"><table border="1">';
    $allMeetings .= '<tr class="tr"><th>Masseur name</th><th>Tell</th><th>Mail</th><th>Time</th><th>date</th><th>Select</th><th>cancel</th></tr>';

    foreach ($meetingsDayData as $meeting) {
      $allMeetings .= '<tr>';
      $allMeetings .= '<td>' . $meeting['fname'] . " " . $meeting['lname'] . '</td>';
      $allMeetings .= '<td>' . $meeting['tell'] . '</td>';
      $allMeetings .= '<td>' . $meeting['email'] . '</td>';
      $allMeetings .= '<td>' . $meeting['meeting_time'] . '</td>';
      $allMeetings .= '<td>' . $meeting['meeting_date'] . '</td>';
      $allMeetings .= '<input type="hidden" name="date" value="' . $meeting['meeting_date'] . '">';
      $allMeetings .= '<input type="hidden" name="meetingTime" value="' . $meeting['meeting_time'] . '">';
      $allMeetings .= '<td><input type="checkbox" name="meetingID[]" value="' . $meeting['meetingID'] . '"></td>';
      $allMeetings .= '<td><input type="hidden" name="userID[]" value="' . $meeting['userID'] . '"><button type="submit" name="deleteMeeting">Del</button></td>';
      $allMeetings .= '</tr>';
    }

    $allMeetings .= '</table></form>';

    echo $header . $allMeetings . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
    exit;
  }

}


function deleteMeeting($pdo, $header, $footer, $userMail)
{
  try {
    $meetingIDs = $_POST['meetingID'];
    $userIDs = $_POST['userID'];
    $userEmail = $userMail; // Added to retrieve the user email
    $meetingDate = $_POST['date'];
    $meetingTime = $_POST['meetingTime'];

    $currentDate = date('Y-m-d');

    foreach ($meetingIDs as $id) {
      // Check if the meeting date is in the past
      $stmt_check_date = $pdo->prepare("SELECT meeting_date FROM meetings WHERE meetingID = :meetingID");
      $stmt_check_date->bindParam(':meetingID', $id, PDO::PARAM_INT);
      $stmt_check_date->execute();
      $meeting = $stmt_check_date->fetch(PDO::FETCH_ASSOC);

      if ($meeting['meeting_date'] < $currentDate) {
        echo '<script>alert("You can delete only future meetings!");</script>';
        allMeetings($header, $footer);
        exit;
      }
    }

    // Ensure that variables are arrays
    if (!is_array($meetingIDs) || !is_array($userIDs)) {
      echo $header . "<main><h1>Invalid input data</h1></main>" . $footer;
      return;
    }

    // Check if at least one checkbox is checked
    if (!empty ($meetingIDs)) {
      // Prepare the delete statement
      $stmt_delete_meetings = $pdo->prepare("DELETE FROM meetings WHERE meetingID = :meetingID");

      // Bind the parameter and execute the statement for each selected meeting
      foreach ($meetingIDs as $key => $id) {
        $stmt_delete_meetings->bindParam(':meetingID', $id, PDO::PARAM_INT);
        $stmt_delete_meetings->execute();
      }

      // Send email notification
      $to = $userEmail;
      $subject = "Meeting canceled! on Optimed";
      $message = "Meeting scheduled for " . $meetingDate . " at " . $meetingTime . " has been canceled. If you need to contact us, you can visit our site or contact us by phone: 0502196936.";
      $headers = "From: egordyai88@gmail.com";
      //mail($to, $subject, $message, $headers);  // Open after configuring SMTP

      // Refresh the page to show the updated list of meetings
      echo '<script>alert("Deleted successfully.");</script>';
      allMeetings($header, $footer);
      exit;
    } else {
      echo $header . '<main><h1>No meetings selected!</h1></main>' . $footer;
      exit;
    }
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}

function showCalendar($pdo, $currentMonth = null, $currentYear = null, $header, $footer)
{

  function masseurData($masseurID, $pdo)
  {
    try {
      // Fetch masseur's details based on the selected masseurID
      $stmt_masseurDetails = $pdo->prepare("SELECT  fname, lname FROM masseurs WHERE masseurID = :masseurID");
      $stmt_masseurDetails->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
      $stmt_masseurDetails->execute();
      $masseurData = $stmt_masseurDetails->fetch(PDO::FETCH_ASSOC);

      if ($masseurData) {
        // If masseur data is found, echo ID, first name, and last name
        $masseurFname = $masseurData['fname'];
        $masseurLname = $masseurData['lname'];
        return $masseurFname . " " . $masseurLname;
      }
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }
  if (isset ($_POST['selectedMasseur'])) {
    $masseurID = $_POST['selectedMasseur'];
    $masseurName = masseurData($masseurID, $pdo);
  }

  if (isset ($_POST['getNextMonth']) || isset ($_POST['getPrevMonth'])) {
    $masseurID = $_POST['masseurID'];
    $masseurName = masseurData($masseurID, $pdo);
  }


  try {
    // Get the current month and year
    if ($currentMonth === null || $currentYear === null) {
      $currentMonth = date('m'); // Change 'n' to 'm' to ensure leading zeros
      $currentYear = date('Y');
    }

    // Calculate the previous and next months
    $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
    $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;
    $nextMonth = ($currentMonth == 12) ? 1 : $currentMonth + 1;
    $nextYear = ($currentMonth == 12) ? $currentYear + 1 : $currentYear;


    // Get the number of days in the current month
    $numDays = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);

    // Get the name of the current month
    $monthName = date('F', mktime(0, 0, 0, $currentMonth, 1, $currentYear));

    // Generate the calendar view for the current month
    $calendar = '<div id="popup"></div><div id="masseurID">'. $masseurID . '</div><table>';
    $calendar .= '<caption><p>Dairy of ' . $masseurName . "</p>";

    // Add condition to show or hide previous month button
    if ($currentYear <= date('Y') && $currentMonth == date('n')) {
      $calendar .= '<div class="caption-row">';
      $calendar .= '<div id="monthName">' . $monthName . '</div><div id="currentYear">' . $currentYear . '</div>';
      $calendar .= '<form  method="post"> 
                      <input type="hidden" name="masseurID" value="' . $masseurID . '">
                      <button class="captionBtn" type="submit" name="getNextMonth">
                      <input type="hidden" name="nextMonth" value="' . $nextMonth . '">
                      <input type="hidden" name="nextYear" value="' . $nextYear . '">&gt;</button></form>';
      $calendar .= '</div>'; // Close caption-row div
    } else {
      $calendar .= '<div class="caption-row">
                     <form method="post"><input type="hidden" name="masseurID" value="' . $masseurID . '">
                     <button class="captionBtn" type="submit" name="getPrevMonth">
                     <input type="hidden" name="prevMonth" value="' . $prevMonth . '">
                     <input type="hidden" name="prevYear" value="' . $prevYear . '">&lt;</button></form>';
      $calendar .= '<div id="monthName">' . $monthName . '</div><div id="currentYear">' . $currentYear . '</div>';
      $calendar .= '<form  method="post">
                      <button class="captionBtn" type="submit" name="getNextMonth">
                      <input type="hidden" name="nextMonth" value="' . $nextMonth . '">
                      <input type="hidden" name="masseurID" value="' . $masseurID . '">
                      <input type="hidden" name="nextYear" value="' . $nextYear . '">&gt;</button></form>';
      $calendar .= '</div>'; // Close caption-row div
    }




    $calendar .= '</caption>';

    $calendar .= '<tr class="tr"><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';

    // Get the day of the week for the first day of the month
    $firstDayOfWeek = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));

    // Add empty cells for days before the first day of the month
    for ($i = 0; $i < $firstDayOfWeek; $i++) {
      $calendar .= '<td></td>';
    }

    // Populate the calendar grid with the days of the month
    for ($day = 1; $day <= $numDays; $day++) {
      $meetingDay = getMeetingDay($day, $currentMonth, $currentYear, $masseurID, $pdo);
      $holiday = getMasseurHolidays($pdo, $masseurID, $monthName, $currentYear, $day);
      // Check if it's a holiday
      if ($holiday) {
        // Display holiday information
        $calendar .= '<td style="background-color: red;"><p>' . $day . '</p><p>Holiday</p></td>';
      } else {
        // Display meeting data
        $numMeetings = 12 - count($meetingDay['meetingsExist']);
        if ($numMeetings == 0) {
          $calendar .= '<td style="background-color: ' . $meetingDay['background'] . ';">';
          $calendar .= '<p>' . $day . '</p><p style="color: black;">No meetings left</p>';

        } elseif ($numMeetings == 12) {
          $calendar .= '<td class="day" id="' . $day . '" style="background-color: ' . $meetingDay['background'] . ';">';
          $calendar .= '<p style="font-weight: bold;">' . $day . '</p><p style="color: white;">Free time</p>';
          $calendar .= '<div class="meetingsExist">';
          foreach ($meetingDay['meetingsExist'] as $meetingTime) {
            $calendar .= '<div class="meetingTime">' . $meetingTime . '</div>'; // Wrap meeting times in <span> tags and add class="meetingTime"
          }
          $calendar .= '</div>';
        } else {
          $calendar .= '<td class="day" id="' . $day . '" style="background-color: ' . $meetingDay['background'] . ';">';
          $calendar .= '<p>' . $day . '</p><p style="color: black;">' . $numMeetings . ' meetings left</p>';
          $calendar .= '<div class="meetingsExist">';
          foreach ($meetingDay['meetingsExist'] as $meetingTime) {
            $calendar .= '<div class="meetingTime">' . $meetingTime . '</div>'; // Wrap meeting times in <span> tags and add class="meetingTime"
          }
          $calendar .= '</div>';
        }

      }


      // Start a new row after every 7 days (Sunday to Saturday)
      if (($day + $firstDayOfWeek) % 7 == 0) {
        $calendar .= '</tr><tr>';
      }
    }

    // Add empty cells for remaining days in the last week
    $lastDayOfWeek = date('w', mktime(0, 0, 0, $currentMonth, $numDays, $currentYear));
    for ($i = $lastDayOfWeek + 1; $i < 7; $i++) {
      $calendar .= '<td></td>';
    }

    $calendar .= '</tr>';
    $calendar .= '</table>';

    // Output the calendar
    echo $header . $calendar . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . "Error: " . $e->getMessage() . $footer;
  }
}

function getMeetingDay($day, $currentMonth, $currentYear, $masseurID, $pdo)
{
  try {
    $meetingDay = ["background" => "green", "meetingsExist" => []];


    // Prepare SQL query to count meetings on the given day
    $sql = "SELECT COUNT(*) FROM meetings WHERE masseurID = :masseurID AND YEAR(meeting_date) = :year AND MONTH(meeting_date) = :month AND DAY(meeting_date) = :day";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
    $stmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
    $stmt->bindParam(':day', $day, PDO::PARAM_INT);
    $stmt->execute();
    $meetingCount = $stmt->fetchColumn();

    // Determine background color based on meeting count
    if ($meetingCount > 0 && $meetingCount < 12) {
      $meetingDay['background'] = "blue";
    } elseif ($meetingCount == 12) {
      $meetingDay['background'] = "red";
    }

    // Prepare SQL query to get array of meetings times exist on the given day
    $sql = "SELECT DATE_FORMAT(meeting_time, '%H:%i') AS formatted_time FROM meetings WHERE masseurID = :masseurID AND YEAR(meeting_date) = :year AND MONTH(meeting_date) = :month AND DAY(meeting_date) = :day";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
    $stmt->bindParam(':month', $currentMonth, PDO::PARAM_INT);
    $stmt->bindParam(':day', $day, PDO::PARAM_INT);
    $stmt->execute();
    $dayMeetingsExist = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($dayMeetingsExist) {
      $meetingDay['meetingsExist'] = $dayMeetingsExist;
    }

    return $meetingDay;
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    return ["background" => "black", "meetingsExist" => []]; // Return default data in case of error
  }
}

function getMasseurHolidays($pdo, $masseurID, $currentMonth, $currentYear, $day)  // get holiday date from db
{
  $currentMonth = date('m', strtotime($currentMonth)); // Convert month to digit
  $holiday_date = $currentYear . '-' . $currentMonth . '-' . $day;

  try {
    // Prepare the query to fetch holidays for the specified month and year
    $sql = "SELECT holidayDay FROM holiday WHERE masseurID = :masseurID AND holidayDay = :holidayDay";

    // Bind parameters
    $stmt_check_holidays = $pdo->prepare($sql);
    $stmt_check_holidays->bindParam(':masseurID', $masseurID);
    $stmt_check_holidays->bindParam(':holidayDay', $holiday_date);
    $stmt_check_holidays->execute();

    // Fetch the holiday for the specified day, month, and year
    $holiday = $stmt_check_holidays->fetch(PDO::FETCH_ASSOC);

    // Return the fetched holiday (or null if not found)
    if (empty ($holiday)) {
      return false;
    }
    return true;
  } catch (PDOException $e) {
    // Handle any errors
    echo json_encode(['success' => false, 'message' => 'Error fetching holidays data: ' . $e->getMessage()]);
    return null;
  }
}




echo $header . "<h1>" . $userFname . " Welcome to Optimed!</h1>" . $footer;

?>