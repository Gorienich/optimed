<?php
require_once '..\include\connectDB.php';
 
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mailSubmit'])) {
        $name = $_POST['name'];
        $tel = $_POST['tellphone'];
        $mail = $_POST['email'];
        $topic = $_POST['subject'];
        $details = $_POST['textarea'];

        // Validation
        if (empty($name) || empty($tel) || empty($mail) || empty($topic) || empty($details)) {
            echo '<script>alert("Please ensure you provide correct data!"); window.location.href = "landingPage.php";</script>';
            exit; // Stop further execution
        }

        // Sanitization
        $name = htmlspecialchars(trim($name));
        $tel = htmlspecialchars(trim($tel));
        $mail = htmlspecialchars(trim($mail));
        $topic = htmlspecialchars(trim($topic));
        $details = htmlspecialchars(trim($details));

        // Email headers
        $to = "egordyai88@gmail.com";
        $subject = $topic;
        $message = $details;
        $headers = "From: " . $mail;

        // Send email
        if (mail($to, $subject, $message, $headers)) {
            // Send confirmation email to the user
            $userSubject = "Optimed";
            $userMessage = "Thank you for contacting us! We will get back to you as soon as possible.";
            mail($mail, $userSubject, $userMessage, "From: optimedisra@outlook.com");

            // Redirect to thank-you page
            header('Location: thankYouPage.html');
            exit;
        } else {
            echo '<script>alert("Failed to send email. Please try again later."); window.location.href = "landingPage.php";</script>';
            exit; // Stop further execution
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommendations</title>
	<link rel="stylesheet" href="Recommendations.css" >
</head>

<body>
 <div class="conteiner"> <!-- נביגציה  -->
        <div><a rel="noopener" noreferrerwindow.opener href="..\user\landingPage.php">Optimed</a></div>
        <div><a rel="noopener" noreferrerwindow.opener href="register.php">Registration</a></div>
		<div><a rel="noopener" noreferrerwindow.opener href="login.php">login</a></div>       <!-- redirect to login page -->
    </div>
	<h1>Recommendations</h1>    
<p>
Elena
Professional and kind masseuse, I had back problems for years and <br>
she took care of me with great dedication, I was happy to be her patient.<br>
<span style= "color:aqua;" >Maria Tel Aviv</span>
<br><br><br>
Courteous and professional service at an excellent price,<br>
 highly recommend to anyone<br>
<span style= "color:aqua;" >Anna Petah Tikva</span>
<br><br><br>
I have been a patient at the clinic for years, they always welcome me<br>
 with a big smile with very courteous and professional service,<br>
 I recommend it to everyone, clinic number 1<br>
<span style= "color:aqua;" >Irish Haifa</span>
<br><br><br>
I have been to many treatments, and no one has helped me as much as Elena, <br>
thanks to her I am no longer in pain.<br>
Courteous service, high professionalism, very high level, recommend to everyone.<br>
<span style= "color:aqua;" >Rami Tel Aviv</span>
</p>
<br><br><br>
<h2>Satisfied customers, you also want to write a review<br>
Send us the form here<br><br><br></h2>

<form>
            <div class="formItem">
                <textarea name="textarea" id="textarea" cols="80" rows="10" maxlength="256" placeholder=""
                    autocomplete="off" required >Message*</textarea>
               <!-- <label for="textarea" >Message*</label>-->
            </div>
		
			<br><br><br>
			 <button type="submit" name="mailSubmit">send message</button>
            <button type="reset">reset</button>
 
  
<div class="card">
		<h1>Rate our clinic</h1>
		<br />
		<span onclick="gfg(1)"
			class="star">★
		</span>
		<span onclick="gfg(2)"
			class="star">★
		</span>
		<span onclick="gfg(3)"
			class="star">★
		</span>
		<span onclick="gfg(4)"
			class="star">★
		</span>
		<span onclick="gfg(5)"
			class="star">★
		</span>
		<h3 id="output">
			Rating is: 0/5
		</h3>
	</div>
	<script src="script.js"></script>
	</form>
        </div>
    </div>
	 <div id="div1"></div>
</body>

</html>