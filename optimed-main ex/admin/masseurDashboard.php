<?php
require_once '..\include\connectDB.php';
session_start();


if (!isset ($_SESSION['masseurID']) || !isset ($_SESSION['Email']) || !isset ($_SESSION['masseurName'])) {
  session_destroy();
  header("Location: ../include/login.php");
  exit();
}
$masseurID = $_SESSION['masseurID'];
$messeurEmail = $_SESSION['Email'];
$messeurFname = $_SESSION['masseurName'];
$pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$header = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>dashboard</title>
    <link rel="stylesheet" href="CSS\admin.css">
</head>
<body>
    <nav class="nav">
        <form method="post"><button type="submit" name="celandar">My celandar</button></form>
        <form method="post"><button type="submit" name="allMeetings">My meetings</button></form>
        <form method="post"><button type="submit" name="allClients">My clients</button></form>
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
    exit;
  }

  if (isset ($_POST['celandar'])) {
    showCalendar($masseurID, $pdo, $currentMonth = null, $currentYear = null, $header, $footer);
  }
  if (isset ($_POST['getPrevMonth'])) {
    showCalendar($masseurID, $pdo, $_POST['prevMonth'], $_POST['prevYear'], $header, $footer);
  }
  if (isset ($_POST['getNextMonth'])) {
    showCalendar($masseurID, $pdo, $_POST['nextMonth'], $_POST['nextYear'], $header, $footer);
  }
  if (isset ($_POST['allMeetings'])) {
    AllMeetings($header, $footer);
  }

  if (isset ($_POST['showDayMeetings'])) {
    showDayMeetings($pdo, $masseurID, $header, $footer);
  }

  if (isset ($_POST['allClients'])) {
    AllClients($pdo, $masseurID, $header, $footer);
  }

  if (isset ($_POST['clientPhoneSubmit'])) {
    findUserByPhone($pdo, $masseurID, $header, $footer);
  }

  if (isset ($_POST['clientNameSubmit'])) {
    findUsersByName($pdo, $masseurID, $header, $footer);
  }

  if (isset ($_POST['newMeetingForm'])) {
    newMeeting($masseurID, $pdo, $header, $footer);
  }

  if (isset ($_POST['searchMeetingsDate'])) {
    searchMeetingDay($masseurID, $pdo, $header, $footer);
  }

  if (isset ($_POST['deleteMeeting'])) {
    deleteMeeting( $pdo, $header, $footer);
  }

  if (isset ($_POST['newHoliday'])) {
    createHoliday($pdo, $masseurID, $header, $footer);
  }

  if (isset ($_POST['cancelHoliday'])) {
    cancelHoliday($pdo, $masseurID, $header, $footer);
  }
}

function showCalendar($masseurID, $pdo, $currentMonth = null, $currentYear = null, $header, $footer)
{
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
    $calendar = '<main><div id="popup"></div><table>';
    $calendar .= '<caption>';
    $calendar .= '<div class="caption-row">' . '<form style="width: 3%; position: relative;" method="post"><button type="submit" name="getPrevMonth"><input type="hidden" name="prevMonth" value="' . $prevMonth . '"><input type="hidden" name="prevYear" value="' . $prevYear . '">&lt;</button></form>';
    $calendar .= '<div id="monthName" style="margin-right: 10px; color: white;">' . $monthName . '</div><div style="color: white;" id="currentYear">' . $currentYear . '</div>';
    $calendar .= '<form style="width: 3%;" method="post"><button type="submit" name="getNextMonth"><input type="hidden" name="nextMonth" value="' . $nextMonth . '"><input type="hidden" name="nextYear" value="' . $nextYear . '">&gt;</button></form>';
    $calendar .= '</div>'; // Close caption-row div
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
        $calendar .= '<td class="day" id="' . $day . '" style="background-color: red;"><p>' . $day . '</p><p>Holiday</p></td>';
      } else {
        // Display meeting data
        $numMeetings = count($meetingDay['meetingsExist']);
        $calendar .= '<td class="day" id="' . $day . '" style="background-color: ' . $meetingDay['background'] . ';">';
        $calendar .= '<p>' . $day . '</p><p style="color: black;">' . $numMeetings . ' meetings</p>';
        $calendar .= '<div class="meetingsExist">';
        foreach ($meetingDay['meetingsExist'] as $meetingTime) {
          $calendar .= '<span class="meetingTime">' . $meetingTime . '</span><br>'; // Wrap meeting times in <span> tags and add class="meetingTime"
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
    $calendar .= '</table></main><script src="masseur.js"></script>';

    // Output the calendar
    echo $header . $calendar . $footer;
    exit;
  } catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
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

function createHoliday($pdo, $masseurID, $header, $footer)   // create hoilay for masseur
{
  $day = $_POST['meetingDay'];
  $month = $_POST['meetingMonth'];
  $year = $_POST['meetingYear'];

  $day = trim($day);
  $month = trim($month);
  $year = trim($year);
  $day = htmlspecialchars($day);
  $month = htmlspecialchars($month);
  $year = htmlspecialchars($year);

  if (empty ($day) || empty ($month) || empty ($year)) {
    echo '<script>alert("Please check holiday date."); window.location.href = "masseurDashboard.php";</script>';
    return;
  }

  $month = date('m', strtotime($month)); // convert month to digit
  $holiday_date = $year . '-' . $month . '-' . $day;

  try {
    // Check if the holiday already exists for this masseur on the specified date
    $stmt_check_holiday = $pdo->prepare("SELECT * FROM holiday WHERE masseurID = :masseurID AND holidayDay = :holiday_date");
    $stmt_check_holiday->bindParam(':masseurID', $masseurID);
    $stmt_check_holiday->bindParam(':holiday_date', $holiday_date);
    $stmt_check_holiday->execute();
    $holiday_exists = $stmt_check_holiday->fetch(PDO::FETCH_ASSOC);

    // Response to the check
    if ($holiday_exists) {
      echo '<script>alert("holiday exist for this day."); window.location.href = "masseurDashboard.php";</script>';
    } else {
      // Insert the new holiday into the database
      $stmt_insert_holiday = $pdo->prepare("INSERT INTO holiday (masseurID, holidayDay) VALUES (:masseurID, :holiday_date)");
      $stmt_insert_holiday->bindParam(':masseurID', $masseurID);
      $stmt_insert_holiday->bindParam(':holiday_date', $holiday_date);
      $stmt_insert_holiday->execute();

      showCalendar($masseurID, $pdo, $currentMonth = null, $currentYear = null, $header, $footer);
      exit;

    }
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}

function cancelHoliday($pdo, $masseurID, $header, $footer)
{
  $day = $_POST['meetingDay'];
  $month = $_POST['meetingMonth'];
  $year = $_POST['meetingYear'];

  $day = htmlspecialchars(trim($day));
  $month = htmlspecialchars(trim($month));
  $year = htmlspecialchars(trim($year));

  // Check if any of the date components are empty
  if (empty ($day) || empty ($month) || empty ($year)) {
    echo '<script>alert("Please check holiday date.");</script>';
    return;
  }

  $month = date('m', strtotime($month)); // convert month to digits
  $holidayDate = $year . '-' . $month . '-' . $day;
  try {
    // Delete holiday
    $stmt_delete_holiday = $pdo->prepare("DELETE FROM holiday WHERE masseurID = :masseurID AND holidayDay = :holidayDay");
    $stmt_delete_holiday->bindParam(':masseurID', $masseurID);
    $stmt_delete_holiday->bindParam(':holidayDay', $holidayDate);

    $stmt_delete_holiday->execute();

    // Check if any rows were affected
    $rows_deleted = $stmt_delete_holiday->rowCount();

    if ($rows_deleted > 0) {
      showCalendar($masseurID, $pdo, $currentMonth = null, $currentYear = null, $header, $footer);
      exit;
    } else {
      echo '<script>alert("no holidays found for this day.");</script>';
      return;
    }
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}
function newMeeting($masseurID, $pdo, $header, $footer)  // create new meeting function
{

  $day = $_POST['meetingDay'];
  $month = $_POST['meetingMonth'];
  $year = $_POST['meetingYear'];
  $userTel = $_POST['UserTell'];
  $meetingTime = $_POST['meetingsTime'];

  $day = trim($day);
  $month = trim($month);
  $year = trim($year);
  $userTel = trim($userTel);
  $meetingTime = trim($meetingTime);

  $day = htmlspecialchars($day);
  $month = htmlspecialchars($month);
  $year = htmlspecialchars($year);
  $userTel = htmlspecialchars($userTel);
  $meetingTime = htmlspecialchars($meetingTime);

  // Check if any of the date components are empty
  if (empty ($day) || empty ($month) || empty ($year) || empty ($meetingTime) || empty ($userTel) || empty ($meetingTime)) {
    echo '<script>alert("Invalid data of meeting provided.");</script>';
    return;
  }

  $month = date('m', strtotime($month)); // convet month to diggit
  $meeting_date = $year . '-' . $month . '-' . $day;

  try {
    // Check if the tel exists in users
    $stmt_check_tel = $pdo->prepare("SELECT * FROM users WHERE tell = :tell");
    $stmt_check_tel->bindParam(':tell', $userTel);
    $stmt_check_tel->execute();
    $user_exists = $stmt_check_tel->fetch(PDO::FETCH_ASSOC);
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
      echo "Meeting already exists for this time";
    } elseif ($user_exists == null) {
      echo '<script>alert("User with provided telephone number does not exist."); window.location.href = "masseurDashboard.php";</script>';
    } else {
      // valid parameters insert meeting in db
      $meeting_date = "$year-$month-$day";
      $stmt = $pdo->prepare("INSERT INTO meetings (masseurID, userID, meeting_date, meeting_time) 
      VALUES (:masseurID, :userID, STR_TO_DATE(:meeting_date, '%Y-%m-%d'), :meeting_time)");
      $stmt->bindParam(':masseurID', $masseurID);
      $stmt->bindParam(':userID', $user_exists['userID']);

      $stmt->bindParam(':meeting_date', $meeting_date);
      $stmt->bindParam(':meeting_time', $meetingTime);
      $stmt->execute();

      $to = $user_exists['email'];
      $subject = "Meeting canceled! on Optimed";
      $message = "Created new meeting for " . $meeting_date . " at " . $meetingTime . " has been canceled. If you need to contact us, you can visit our site or contact us by phone: 0502196936.";
      $headers = "From: egordyai88@gmail.com";
      //mail($to, $subject, $message, $headers);   open after config SMTP


      showCalendar($masseurID, $pdo, $month, $year, $header, $footer);
      exit;
    }
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}
function AllMeetings($header, $footer)
{
  echo $header . '<main><form method="post">
       <input style="margin: 100px 40% 20px 40%; width: 20%;" type="date" name="selectedDate">
       <button style="margin: 20px 40%; width: 20%;" type="submit" name="searchMeetingsDate">find</button></form>' . $footer;
  exit;
}

function searchMeetingDay ($masseurID, $pdo, $header, $footer)
{
    if (!isset($_POST['selectedDate'])) {
        echo '<script>alert("Incorrect date format"); window.location.href = "masseurDashboard.php";</script>';
        return;
    }

    $dateData = $_POST['selectedDate'];
    $dateData = htmlspecialchars(trim($dateData));
    // Convert date format from day.month.year to year-month-day
    $dateData = date('Y-m-d', strtotime($dateData));

    try {
        // Fetch all meetings of a day for the given masseur and order by meeting time
        $stmt_check_meetings = $pdo->prepare("SELECT meetings.meetingID, meetings.userID, users.username, users.lname, users.tell, users.email, DATE_FORMAT(meetings.meeting_time, '%H:%i') AS meeting_time FROM meetings INNER JOIN users ON meetings.userID = users.userID WHERE meetings.masseurID = :masseurID AND meetings.meeting_date = :meeting_date ORDER BY meetings.meeting_time");
        $stmt_check_meetings->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
        $stmt_check_meetings->bindParam(':meeting_date', $dateData, PDO::PARAM_STR); // Corrected parameter name
        $stmt_check_meetings->execute();
        $meetingsDayData = $stmt_check_meetings->fetchAll(PDO::FETCH_ASSOC);

        // Check if any meetings exist for the given day
        if (!$meetingsDayData) {
            echo $header . "<main><h1>No meetings for ". $dateData . "</h1></main>" . $footer;
            exit;
        }

        // Build the HTML table for displaying meetings
        $allMeetings = '<main><h1>meetings for '. $dateData . '</h1><table border="1">';
        $allMeetings .= '<tr class="tr"><th>Client name</th><th>Tell</th><th>Mail</th><th>Time</th><th>Select</th><th>Delete</th></tr>';

        foreach ($meetingsDayData as $meeting) {
          $allMeetings .= '<tr><form method="post"><input type="hidden" name="date" value="' . $dateData . '">';
          $allMeetings .= '<input type="hidden" name="email" value="' . $meeting['email'] . '">'; // Add email field
          $allMeetings .= '<input type="hidden" name="meetingTime" value="' . $meeting['meeting_time'] . '">'; // Add meeting time field
          $allMeetings .= '<td>' . $meeting['username'] . " " . $meeting['lname'] . '</td>';
          $allMeetings .= '<td>' . $meeting['tell'] . '</td>';
          $allMeetings .= '<td name="email">' . $meeting['email'] . '</td>';
          $allMeetings .= '<td name="meetingTime">' . $meeting['meeting_time'] . '</td>';
          $allMeetings .= '<td><input type="checkbox" name="meetingID[]" value="' . $meeting['meetingID'] . '"></td>';
          $allMeetings .= '<td><input type="hidden" name="userID[]" value="' . $meeting['userID'] . '"><button type="submit" name="deleteMeeting">Del</button></td>';
          $allMeetings .= '</form></tr>';
        }

        $allMeetings .= '</table></main>';

        echo $header . $allMeetings . $footer;
        exit;
    } catch (PDOException $e) {
        echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
        exit;
    }
}



function showDayMeetings($pdo, $masseurID, $header, $footer)
{
  // Check if the required POST data is set
  if (!isset ($_POST['meetingDay']) || !isset ($_POST['meetingMonth']) || !isset ($_POST['meetingYear'])) {
    echo '<script>alert("Check date");</script>';
    return;
  }


  $day = htmlspecialchars(trim($_POST['meetingDay']));
  $month = htmlspecialchars(trim($_POST['meetingMonth']));
  $year = htmlspecialchars(trim($_POST['meetingYear']));

  // Check if any of the date components are empty
  if (empty ($day) || empty ($month) || empty ($year)) {
    echo '<script>alert("Check date");</script>';
    return;
  }

  // Convert month to digit
  $month = date('m', strtotime($month));
  $meeting_date = $year . '-' . $month . '-' . $day;

  try {
    // Fetch all meetings of a day for the given masseur and order by meeting time
    $stmt_check_meetings = $pdo->prepare("SELECT meetings.meetingID, meetings.userID, users.username, users.lname, users.tell, users.email, DATE_FORMAT(meetings.meeting_time, '%H:%i') AS meeting_time FROM meetings INNER JOIN users ON meetings.userID = users.userID WHERE meetings.masseurID = :masseurID AND meetings.meeting_date = :meeting_date ORDER BY meetings.meeting_time");
    $stmt_check_meetings->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt_check_meetings->bindParam(':meeting_date', $meeting_date, PDO::PARAM_STR);
    $stmt_check_meetings->execute();
    $meetingsDayData = $stmt_check_meetings->fetchAll(PDO::FETCH_ASSOC);

    // Check if any meetings exist for the given day
    if (!$meetingsDayData) {
      echo $header . "<main><h1>No meetings yet for " . $day . "/" . $month . "/" . $year . "</h1></main>" . $footer;
      exit;
    }

    // Build the HTML table for displaying meetings
    $dayMeetingsTable = '<main><table border="1">';
    $dayMeetingsTable .= '<tr class="tr"><th>Client name</th><th>Tell</th><th>Mail</th><th>Time</th><th>Select</th><th>Delete</th></tr>';

    foreach ($meetingsDayData as $meeting) {
      $dayMeetingsTable .= '<tr><form method="post"><input type="hidden" name="date" value="' . $meeting_date . '">';
      $dayMeetingsTable .= '<input type="hidden" name="email" value="' . $meeting['email'] . '">'; // Add email field
      $dayMeetingsTable .= '<input type="hidden" name="meetingTime" value="' . $meeting['meeting_time'] . '">'; // Add meeting time field
      $dayMeetingsTable .= '<td>' . $meeting['username'] . " " . $meeting['lname'] . '</td>';
      $dayMeetingsTable .= '<td>' . $meeting['tell'] . '</td>';
      $dayMeetingsTable .= '<td name="email">' . $meeting['email'] . '</td>';
      $dayMeetingsTable .= '<td name="meetingTime">' . $meeting['meeting_time'] . '</td>';
      $dayMeetingsTable .= '<td><input type="checkbox" name="meetingID[]" value="' . $meeting['meetingID'] . '"></td>';
      $dayMeetingsTable .= '<td><input type="hidden" name="userID[]" value="' . $meeting['userID'] . '"><button type="submit" name="deleteMeeting">Del</button></td>';
      $dayMeetingsTable .= '</form></tr>';
    }
    

    $dayMeetingsTable .= '</table></main>';

    echo $header . $dayMeetingsTable . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
    exit; // Terminate script execution after redirect
  }
}

function deleteMeeting($pdo,  $header, $footer)
{
  try {
    if (!isset ($_POST['meetingID']) || !isset ($_POST['userID']) || !isset ($_POST['date']) || !isset ($_POST['meetingTime'])) {
      echo '<script>alert("Lost meeting data");"</script>';

    }

    $meetingIDs = $_POST['meetingID'];
    $userIDs = $_POST['userID'];
    $userEmail = $_POST['email'];
    $meetingDate = $_POST['date'];
    $meetingTime = $_POST['meetingTime'];

    // Ensure that variables are arrays
    if (!is_array($meetingIDs) || !is_array($userIDs)) {
      echo $header . "<main><h1>Invalid input data</h1></main>" . $footer;
      exit;
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

      $to = $userEmail;
      $subject = "Meeting canceled! on Optimed";
      $message = "Meeting scheduled for " . $meetingDate . " at " . $meetingTime . " has been canceled. If you need to contact us, you can visit our site or contact us by phone: 0502196936.";
      $headers = "From: egordyai88@gmail.com";
      //mail($to, $subject, $message, $headers);  open after config SMTP

      // Refresh the page to show the updated list of meetings
      echo '<script>alert("Deleted succsessfuly.");</script>';

    } else {
      echo $header . '<main><h1>No meetings selected!</h1></main>' . $footer;
      exit;
    }
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}



function AllClients($pdo, $masseurID, $header, $footer)
{
  try {
    // Fetch all users who have meetings with the given masseur
    $stmt_all_clients = $pdo->prepare("
            SELECT DISTINCT users.userID, users.username, users.lname, users.tell, users.email
            FROM meetings
            INNER JOIN users ON meetings.userID = users.userID
            WHERE meetings.masseurID = :masseurID
        ");
    $stmt_all_clients->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt_all_clients->execute();
    $allClientsData = $stmt_all_clients->fetchAll(PDO::FETCH_ASSOC);

    // Check if any clients exist for the given masseur
    if (!$allClientsData) {
      echo $header . "<main><h2>No clients yet.</h2></main>" . $footer;
      exit;
    }
    $clientsTable = '<main><form id="findClientByPhoneForm" method="post" style="margin: 10px auto; width: 50%;">
        <label style="font-weight: bold;" for="clientPhone">Search client by phone:</label>
        <input style="margin: 5px; text-align: center; padding: 5px;" type="phone" id="clientPhone" name="clientPhone" placeholder="enter phone number" pattern="[0-9]{10}" title="phone number have to be a 10-digit number" maxlength="10" required>
        <button style="margin-left: 10px;" type="submit" name="clientPhoneSubmit">go</button>
        </form>';

    $clientsTable .= '<form id="findClientByNameForm" method="post"  style="margin: 10px auto; width: 50%;">
        <label style="font-weight: bold;" for="clientName">Search client by name:</label>
        <input style="margin: 5px 12px;  text-align: center; padding: 5px;" type="text" id="clientName" name="clientName"  placeholder="enter client name" maxlength="50" required>
        <button type="submit" name="clientNameSubmit">go</button>
        </form>';
    // Build the HTML table for displaying clients
    $clientsTable .= '<table border="1">';
    $clientsTable .= '<tr class="tr"><th>Client name</th><th>Tell</th><th>Mail</th>';

    foreach ($allClientsData as $client) {
      $clientsTable .= '<tr><form method="post">';
      $clientsTable .= '<td>' . $client['username'] . " " . $client['lname'] . '</td>';
      $clientsTable .= '<td>' . $client['tell'] . '</td>';
      $clientsTable .= '<td>' . $client['email'] . '</td>';
      $clientsTable .= '</tr>';
    }

    $clientsTable .= '</table></main>';

    echo $header . $clientsTable . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}

function findUserByPhone($pdo, $masseurID,$header, $footer)   // find client 
{
  $clientPhone = $_POST['clientPhone'];
  $clientPhone = htmlspecialchars(trim($clientPhone));

  if ($clientPhone == "") {
    echo '<script>alert("check phone data"); window.location.href = "masseurDashboard.php";</script>';
    return;
  }

  try {
    // Fetch users who have meetings with the given masseur and have the provided phone number
    $stmt_all_clients = $pdo->prepare("
            SELECT DISTINCT users.userID, users.username, users.lname, users.tell, users.email
            FROM meetings
            INNER JOIN users ON meetings.userID = users.userID
            WHERE meetings.masseurID = :masseurID AND users.tell = :clientPhone
        ");
    $stmt_all_clients->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt_all_clients->bindParam(':clientPhone', $clientPhone, PDO::PARAM_STR);
    $stmt_all_clients->execute();
    $allClientsData = $stmt_all_clients->fetchAll(PDO::FETCH_ASSOC);

    // Check if any clients exist for the given masseur and phone number
    if (!$allClientsData) {
      echo '<script>alert("No client by the number' . $clientPhone . '");</script>';
      return;
    }

    // Build the HTML table for displaying clients
    $clientsTable = '<main><table border="1">';
    $clientsTable .= '<tr class="tr"><th>Client name</th><th>Tell</th><th>Mail</th>';

    foreach ($allClientsData as $client) {
      $clientsTable .= '<tr><form method="post">';
      $clientsTable .= '<td>' . $client['username'] . " " . $client['lname'] . '</td>';
      $clientsTable .= '<td>' . $client['tell'] . '</td>';
      $clientsTable .= '<td>' . $client['email'] . '</td>';
      $clientsTable .= '</tr>';
    }

    $clientsTable .= '</table></main>';
    echo $header . $clientsTable . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}

function findUsersByName($pdo, $masseurID, $header, $footer) // find client
{
  $clientName = $_POST['clientName'];
  $clientName = htmlspecialchars(trim($clientName));

  if ($clientName == "") {
    echo '<script>alert("insure the name is correct!");</script>';
    return;
  }

  try {
    // Fetch users who have meetings with the given masseur and have the provided client name
    $stmt_all_clients = $pdo->prepare("
            SELECT DISTINCT users.userID, users.username, users.lname, users.tell, users.email
            FROM meetings
            INNER JOIN users ON meetings.userID = users.userID
            WHERE meetings.masseurID = :masseurID AND users.username LIKE CONCAT('%', :clientName, '%')
        ");
    $stmt_all_clients->bindParam(':masseurID', $masseurID, PDO::PARAM_INT);
    $stmt_all_clients->bindParam(':clientName', $clientName, PDO::PARAM_STR);
    $stmt_all_clients->execute();
    $allClientsData = $stmt_all_clients->fetchAll(PDO::FETCH_ASSOC);

    // Check if any clients exist for the given masseur and client name
    if (!$allClientsData) {
      echo $header . "<main><h1>No client found for this name</main></h1>" . $footer;
      exit;
    }

    // Build the HTML table for displaying clients
    $clientsTable = '<main><table border="1">';
    $clientsTable .= '<tr class="tr"><th>Client name</th><th>Tell</th><th>Mail</th>';

    foreach ($allClientsData as $client) {
      $clientsTable .= '<tr><form method="post">';
      $clientsTable .= '<td>' . $client['username'] . " " . $client['lname'] . '</td>';
      $clientsTable .= '<td>' . $client['tell'] . '</td>';
      $clientsTable .= '<td>' . $client['email'] . '</td>';
      $clientsTable .= '</tr>';
    }

    $clientsTable .= '</table></main>';
    echo $header . $clientsTable . $footer;
    exit;
  } catch (PDOException $e) {
    echo $header . '<main><h1>Error occurred: ' . $e->getMessage() . '</h1></main>' . $footer;
  }
}


echo $header . "<main><h1>" . $messeurFname . " Welcome to Optimed!</h1><h2>System is Ready...</h2></main>" . $footer;

?>
