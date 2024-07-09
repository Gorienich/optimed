<?php
require_once '..\include\connectDB.php';

session_start();
$email = isset($_SESSION['Email']) ? $_SESSION['Email'] : '';
function checkUser($email)
{
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=optimed", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $user = "unknown";

        if ($email === "egordyai88@gmail.com") {
            return "admin";
        }

        $stmt_check_email_user = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt_check_email_user->bindParam(':email', $email);
        $stmt_check_email_user->execute();
        $email_exists_user = $stmt_check_email_user->fetchColumn();

        if ($email_exists_user) {
            return "user";
        }

        $stmt_check_email = $pdo->prepare("SELECT COUNT(*) FROM masseurs WHERE email = :email");
        $stmt_check_email->bindParam(':email', $email);
        $stmt_check_email->execute();
        $email_exists = $stmt_check_email->fetchColumn();

        if ($email_exists) {
            return "masseur"; // Corrected return value to "masseur"
        }

        return $user;
    } catch (PDOException $e) {
        $errorForm = "Error: " . $e->getMessage();
        return $errorForm;
    }
}

$user = checkUser($email);

?>
<?php

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
    <title>optimed</title>
    <link rel="stylesheet" href="userCss\style.css">
</head>
<noscript>alert("to use our web site turn on java script in browser!!!");</noscript>

<body>
    <div class="headerOpen" id="headerOpener">menu</div>

    <header id="header">
        <div class="closeHeaderOpen" id="closeHeaderOpener">x</div>
       <a rel="noopener" noreferrerwindow.opener href="#contactForm">Contact Us</a>
	 
         <a rel="noopener" noreferrerwindow.opener href="..\include\login.php">Login</a>

        <?php if ($user === "user"): ?>
            <a rel="noopener" noreferrerwindow.opener href="userDashboard.php">My Space</a>
        <?php elseif ($user === "admin"): ?>
            <a rel="noopener" noreferrerwindow.opener href="../admin/adminDashboard.php">My Space</a>
        <?php elseif ($user === "masseur"): ?>
            <a rel="noopener" noreferrerwindow.opener href="../admin/masseurDashboard.php">My Space</a>
        <?php endif; ?>

        <a rel="noopener" noreferrerwindow.opener href="..\include\register.php">Sign Up</a>
        <a rel="noopener" noreferrerwindow.opener href="#aboutUs">About Us</a>
		<a rel="noopener" noreferrerwindow.opener href="..\include\Recommendations.php">Recommendations</a>

		
    </header>

    <h2  title="masseurs of optimed ">Our Masseurs</h2>
    <!--   masseurs conteiner  -->
    <div class="masseur-conteiner">
        <button class="btn-prev controlls">&lt;</button>
        <div class="masseur-foto">
            <div class="slider-item">
                <img class="masseur-image" src="..\admin\CSS\pic\masseursPic\massajist1.jpg" alt="Masseur Image">
            </div>
            <div class="slider-item">
                <img class="masseur-image" src="..\admin\CSS\pic\masseursPic\massajist2.jpg" alt="Masseur Image">
            </div>
        </div>
        <div class="masseur-room">
            <img class="masseur-room-img" src="..\admin\CSS\pic\masseurRoom\room1.jpg" alt="Masseur Room">
            <img class="masseur-room-img" src="..\admin\CSS\pic\masseurRoom\room2.jpg" alt="Masseur Room">
        </div>
        <button class="btn-next controlls">&gt;</button>
    </div>
    <div>
        <p id="masseurDescription"></p>	
    </div>
    <!-- end of masseurs container -->
    <?php if ($user === "user" || $user === "unknown"): ?>
        <div class="getMeeting"><a href="userDashboard.php">Get Meeting</a></div>
    <?php endif; ?>


    <h2 title="about optimed" id="aboutUs">About US</h2>
    <div class="aboutOptimed">
        <div class="optimedText">
            <br>
            <span>
                <h3 ><b>Providing Healing Massages Since 2001</b></h3>
            </span>
            
            <p>At Optimed, we have been dedicated to the art of healing and relaxation since our establishment in 2001.
                For over two decades, we have been the trusted oasis for individuals seeking rejuvenation and relief
                from the stresses of everyday life.</p>
            <span>
                <h3><b>Our Mission</b></h3>
            </span>
       
            <p>Our mission is to promote holistic well-being and bring harmony to your mind, body, and spirit through
                the power of therapeutic touch. We are committed to delivering high-quality massage therapies that
                provide the ultimate relaxation experience.</p>
            <span>
                <h3><b>Experienced Practitioners</b></h3>
            </span>
            <p>Our team of experienced and certified massage therapists brings a wealth of knowledge and skill to every
                session. They are passionate about tailoring each massage to meet the unique needs of our clients,
                whether it's for pain relief, stress reduction, or pure relaxation.</p>
			
       </div>
		
        <div class="optimedPic">
            <img src="userCSS\pic\tempsnip.png" alt="optimed">
        </div>
    </div>
    <div class="aboutOptimed">
        <div class="optimedPic">
            <img src="userCSS\pic\backintroPNG.PNG" alt="optimed">
        </div>
        <div class="optimedText">
            <span>
                <h3><b>A Tranquil Sanctuary</b></h3>
            </span>
            <p>Step into our tranquil sanctuary where a sense of serenity surrounds you. Our clinic's soothing ambiance
                and calming decor provide the perfect setting for you to unwind and release the burdens of the day.</p>
            <span>
                <h3><b>Customized Treatments</b></h3>
            </span>
            <p>We understand that each client is unique, which is why we offer a range of massage modalities and
                treatments. Whether you're seeking a Swedish massage to melt away tension, a deep tissue massage to
                address specific concerns, or a specialty treatment, we have a massage to meet your exact requirements.
            </p>
            <span>
                <h3><b>Community Engagement</b></h3>
            </span>
            <p>Over the years, we've been actively engaged with our local community. We believe in giving back and often
                participate in community events and wellness programs to promote a healthier and happier community.</p>
            <span>
                <h3><b>Your Journey to Wellness Begins Here</b></h3>
            </span>
            <p>At Optimed, we're not just a massage clinic; we're your partners in your journey to wellness. When you
                enter our doors, you become a part of our extended family, and we are committed to helping you achieve a
                healthier, more balanced life.
                Thank you for choosing us. We look forward to serving you and helping you on your path to a more relaxed
                and revitalized you.</p>
        </div>
    </div>
    <h2 id="contactForm">Contact Us</h2>
    <!--   contact form    -->
    <div class="form-conteiner">
        <form class="contact-form" method="post" enctype="multipart/form-data">
            <div class="formItem">
                <input type="text" id="name" name="name" placeholder="" autocomplete="on" required>
                <label for="name">Name*</label>
            </div>
            <div class="formItem">
                <input type="email" id="email" name="email" placeholder="" autocomplete="on" required>
                <label for="email">Email*</label>
            </div>
            <div class="formItem">
                <input type="tel" id="phone" name="tellphone" placeholder="" autocomplete="on" pattern="[0-9]{10}"
                    title="phone number have to be a 10-digit number" maxlength="10" required>
                <label for="phone">Phone*</label>
            </div>
            <div class="formItem">
                <input type="text" id="subject" name="subject" maxlength="50" placeholder="" autocomplete="on" required>
                <label for="subject">Subject*</label>
            </div>
            <div class="formItem">
                <textarea name="textarea" id="textarea" cols="30" rows="10" maxlength="256" placeholder=""
                    autocomplete="off" required></textarea>
                <label for="textarea">Message*</label>
				
            </div>
		
            <button type="submit" name="mailSubmit">send message</button>
            <button type="reset">reset</button>
        </form>
        <div class="optimed-map">
            <iframe title="optimed map"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3353.747087242253!2d34.984865400000004!3d32.798964700000006!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x151dbb06345cc8eb%3A0x52ed50b23d866e01!2sSderot%20Moriah%2035%2C%20Haifa!5e0!3m2!1sru!2sil!4v1697407068544!5m2!1sru!2sil"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </div>
    <h2 title="about massage">About massage</h2>
    <!--   conteiner of massage discriptions-->
    <div class="grid-container" id="gridContainer">
        <div class="item aromatherapiya">
            <div class="itemdescript">
                <p>Aromatherapiya</p>
            </div>
            <div class="description">
                <h2 title="description of aromatherapiya">The Key Principles of Aromatherapy</h2>
                <h3>Aromatherapy: Enhancing Well-being with Essential Oils</h3>
                <span>
                    <p><b>Aromatherapy is a holistic healing practice that utilizes the natural aromas and therapeutic
                            properties of essential oils to promote physical, mental, and emotional well-being. This
                            ancient practice has its roots in traditional medicine and has been used for thousands of
                            years to enhance health and treat various conditions. Here's a more detailed description of
                            aromatherapy:</b></p>
                </span>
                <h3>Essential Oils:</h3>
                <span>
                    <p>These are concentrated, volatile compounds extracted from various parts of aromatic plants, such
                        as leaves, flowers, stems, and roots. Each essential oil has a unique chemical composition and
                        therapeutic properties. Popular examples include lavender, tea tree, eucalyptus, and peppermint.
                    </p>
                </span>
                <h3>Inhalation:</h3>
                <span>
                    <p>One of the primary methods of using essential oils is inhalation. The scent molecules stimulate
                        the olfactory system, which is linked to the brain's emotional center. This can trigger
                        emotional and psychological responses, such as relaxation, stress reduction, or increased
                        alertness.</p>
                </span>
                <h3>Topical Application:</h3>
                <span>
                    <p>Essential oils can be diluted and applied to the skin. This method allows for localized treatment
                        and can be used in massages, baths, or as part of skincare routines. The oils are believed to be
                        absorbed through the skin and enter the bloodstream.</p>
                </span>
                <h3>Holistic Healing:</h3>
                <span>
                    <p>Aromatherapy is used to address a wide range of conditions, from stress and anxiety to muscle
                        pain, respiratory issues, and skin problems. It's often considered a complementary therapy
                        alongside conventional medicine.</p>
                </span>
                <h3>Customized Blends:</h3>
                <span>
                    <p>Aromatherapists may create custom blends of essential oils to address specific concerns. These
                        blends can be tailored to an individual's needs, making aromatherapy a highly personalized
                        therapy.</p>
                </span>
                <h3>Safety and Dilution:</h3>
                <span>
                    <p> It's crucial to use essential oils with care, as they are potent. Most essential oils must be
                        diluted in a carrier oil before applying to the skin. Some oils can cause skin irritation or
                        adverse reactions if used undiluted.</p>
                </span>
                <h3>Research and Evidence:</h3>
                <span>
                    <p> Aromatherapy is supported by some scientific research, but it's essential to note that results
                        can vary between individuals. While many people experience positive effects, it's not a
                        replacement for medical treatment in severe health conditions.

                        Aromatherapy is widely used to promote relaxation, relieve stress, and improve mood. Its
                        versatility and ability to address a wide range of health concerns make it a popular choice for
                        those seeking natural and holistic approaches to wellness. Whether through diffusers, massage,
                        or topical applications, the art of aromatherapy offers a fragrant path to better health and a
                        heightened sense of well-being.</p>
                </span>
            </div>
        </div>

        <div class="item bumboo">
            <div class="itemdescript">
                <p>Bumboo</p>
            </div>
            <div class="description">
                <h2 title="description of bumboo massage">Key Elements of Bamboo Massage:</h2>
                <h3>Bamboo Massage: A Rejuvenating Journey with Nature's Tools</h3>
                <span>
                    <p>Bamboo massage is a unique and therapeutic bodywork technique that utilizes bamboo sticks and
                        canes of varying shapes and sizes to deliver a rejuvenating and deeply relaxing massage
                        experience. With its origins in Asia, this innovative massage style has gained popularity
                        worldwide for its ability to relieve tension, improve circulation, and promote a profound sense
                        of well-being. Here's an in-depth description of bamboo massage:</p>
                </span>
                <h3>The Art of Bamboo Massage:</h3>
                <span>
                    <p>Bamboo massage is an ancient practice that has been revived and modernized to provide a luxurious
                        and effective massage experience. The central element of this treatment is the use of bamboo
                        sticks and canes, which are carefully selected for their smooth texture and varying lengths.
                        These "tools" become extensions of the therapist's hands and allow for the application of both
                        gentle and deep pressure.</p>
                </span>
                <h3>Natural Elements:</h3>
                <span>
                    <p>Bamboo, known for its strength and flexibility, is a symbol of nature's harmony. When used in
                        massage, it brings the qualities of resilience and smoothness to the treatment.</p>
                </span>
                <h3>Deep Tissue Benefits: </h3>
                <span>
                    <p>The firmness and weight of the bamboo sticks make them ideal for deep tissue massage. They can
                        reach and target muscle knots and tension areas more effectively than hands alone.</p>
                </span>
                <h3>Relaxation:</h3>
                <span>
                    <p>Despite the potential for deep pressure, bamboo massage is deeply relaxing. The smooth surface of
                        the sticks glides over the body, inducing a soothing and calming sensation.</p>
                </span>
                <h3>Improved Circulation:</h3>
                <span>
                    <p>The rolling, kneading, and tapping movements of bamboo massage stimulate blood flow and lymphatic
                        drainage. This can help reduce inflammation and detoxify the body.</p>
                </span>
                <h3>Versatility:</h3>
                <span>
                    <p>Bamboo canes of various sizes and shapes are employed, allowing therapists to customize the
                        massage according to the client's needs. The variety of techniques includes both Western and
                        Asian approaches.</p>
                </span>
                <h3>Balancing Energy:</h3>
                <span>
                    <p>Like other forms of massage, bamboo massage aims to balance the body's energy flow. It can
                        relieve stress, anxiety, and emotional blockages.</p>
                </span>
                <h3>Holistic Wellness:</h3>
                <span>
                    <p>Bamboo massage is more than just a physical experience; it addresses the overall well-being of an
                        individual. The connection to nature's elements enhances the massage's sense of balance and
                        harmony.</p>
                </span>
                <h3>Warm Bamboo Option:</h3>
                <span>
                    <p> Some practitioners incorporate heated bamboo sticks, further intensifying the relaxation and
                        comfort of the massage.</p>
                </span>
                <h3>Experience the Rejuvenation:</h3>
                <span>
                    <p>Bamboo massage offers a unique opportunity to connect with the healing power of nature. As the
                        bamboo sticks glide and roll over the body, it's as though you are being transported to a
                        tranquil bamboo grove. This massage not only eases muscle tension but also provides a spiritual
                        connection to the earth's elements. Whether you're seeking relief from muscle pain, stress, or
                        simply a pampering retreat, bamboo massage is a journey to well-being, one that harmonizes your
                        body, mind, and spirit with the gifts of the natural world.</p>
                </span>
            </div>
        </div>

        <div class="item pilate">
            <div class="itemdescript">
                <p>Pilate</p>
            </div>
            <div class="description">
                <h2 title="description about pilates massage">Key Elements of Pilates Massage:</h2>
                <h3>Pilates Massage: Realign, Strengthen, and Transform Your Body</h3>
                <span>
                    <p>Pilates massage, often referred to as Pilates bodywork or Pilates-based bodywork, is a holistic
                        approach to wellness that combines the principles of Pilates exercises with the healing touch of
                        massage therapy. This unique combination offers a comprehensive and transformative experience,
                        promoting not only physical strength but also mental and emotional well-being. Here's a detailed
                        description of Pilates massage:</p>
                </span>
                <h3>The Synergy of Pilates and Massage:</h3>
                <span>
                    <p>Pilates, a mind-body fitness system created by Joseph Pilates, is renowned for its focus on core
                        strength, flexibility, and body awareness. Massage therapy, on the other hand, has long been
                        cherished for its ability to soothe muscles, reduce tension, and promote relaxation. Pilates
                        massage brings these two worlds together to provide a comprehensive approach to wellness.</p>
                </span>
                <h3>Core Strength:</h3>
                <span>
                    <p>Central to Pilates philosophy, core strength is emphasized in Pilates massage. The session begins
                        with core activation, helping you to connect with and engage the muscles of your abdomen, lower
                        back, and pelvic region.</p>
                </span>
                <h3>Flexibility and Mobility:</h3>
                <span>
                    <p>Pilates massage incorporates stretching and flexibility exercises inspired by classical Pilates.
                        These movements aim to enhance your range of motion, ensuring that you feel more limber and free
                        in your movements.</p>
                </span>
                <h3>Mind-Body Connection:</h3>
                <span>
                    <p>Similar to traditional Pilates, Pilates massage places a strong emphasis on body awareness and
                        the mind-body connection. You'll be guided to focus on your breath and movement, enhancing
                        mindfulness.</p>
                </span>
                <h3>Myofascial Release:</h3>
                <span>
                    <p>The massage component of the session may include myofascial release techniques. This addresses
                        muscular tightness and tension, providing relief and relaxation.</p>
                </span>
                <h3>Postural Alignment:</h3>
                <span>
                    <p>Improving posture is a significant goal in Pilates massage. The combination of exercises and
                        massage can help to correct imbalances and enhance your overall posture.</p>
                </span>
                <h3>Whole-Body Integration:</h3>
                <span>
                    <p>Pilates massage aims to work on your whole body rather than isolated muscles. It helps improve
                        overall strength and balance.</p>
                </span>
                <h3>Relaxation:</h3>
                <span>
                    <p>The incorporation of massage ensures that you not only gain strength and flexibility but also
                        experience the relaxation and stress reduction that massage provides.</p>
                </span>
                <h3>Customization:</h3>
                <span>
                    <p>Each Pilates massage session is tailored to the individual's needs and goals, whether it's
                        rehabilitation, fitness, stress relief, or holistic wellness.</p>
                </span>
                <h3>Experience the Transformation:</h3>
                <span>
                    <p>Pilates massage is a holistic experience that guides you towards balance and vitality. Whether
                        you are a seasoned Pilates enthusiast or new to both Pilates and massage, you'll discover that
                        the combination provides a unique journey to well-being. It encourages self-awareness,
                        self-care, and self-empowerment. Through Pilates massage, you can realign, strengthen, and
                        transform your body, bringing harmony to both body and mind.</p>
                </span>
            </div>
        </div>
        <div class="item footMassage">
            <div class="itemdescript">
                <p>Reflexology</p>
            </div>
            <div class="description">
                <h2 title="description of foot massage">Balancing Your Body Through Your Feet:</h2>
                <h3>Foot Massage: The Art of Healing Through Your Soles</h3>
                <span>
                    <p>Foot massage, also known as foot reflexology or foot acupressure, is a time-honored healing art
                        rooted in ancient traditions, believed to promote physical and mental well-being by applying
                        pressure to specific points on the feet. This practice aims to bring balance to the body, reduce
                        stress, and induce relaxation. Here is a detailed description of the wonders of foot massage</p>
                </span>
                <h3>Ancient Wisdom:</h3>
                <span>
                    <p>Foot massage has its roots in various cultures, including Chinese and Egyptian traditions, dating
                        back thousands of years. These cultures believed that the feet are a microcosm of the entire
                        body, and by manipulating specific points on the feet, one can stimulate and restore balance in
                        corresponding organs and systems throughout the body.</p>
                </span>
                <h3>Reflexology Points:</h3>
                <span>
                    <p>The essence of foot massage lies in reflexology points. These are specific areas on the feet that
                        are believed to be connected to other parts of the body. By applying pressure to these points, a
                        skilled foot masseur can help alleviate discomfort, release tension, and promote overall health.
                    </p>
                </span>
                <h3>Pressure and Technique:</h3>
                <span>
                    <p>During a foot massage, a practitioner will use their hands, fingers, and thumbs to apply pressure
                        to various reflexology points on your feet. The amount of pressure and technique used may vary
                        based on your individual needs and preferences. A skilled masseur understands how to adjust
                        their methods to achieve optimal results.</p>
                </span>
                <h3>Relaxation and Stress Reduction:</h3>
                <span>
                    <p>Foot massage provides a profound sense of relaxation. As tension in the feet melts away, it sends
                        signals to the brain to release endorphins, which are natural mood elevators. This can
                        significantly reduce stress and anxiety, improving your overall mental well-being.</p>
                </span>
                <h3>Pain Relief:</h3>
                <span>
                    <p>Foot massage is often sought for pain relief. It can help alleviate symptoms associated with
                        conditions like plantar fasciitis, arthritis, and even chronic headaches. The release of tension
                        in the feet can lead to pain relief throughout the body.</p>
                </span>
                <h3>Improved Circulation:</h3>
                <span>
                    <p>The stimulation of reflexology points enhances blood circulation. This increased blood flow
                        delivers oxygen and nutrients to cells and helps to remove waste and toxins, contributing to
                        better health.</p>
                </span>
                <h3>Detoxification:</h3>
                <span>
                    <p>Foot massage is believed to aid the body's natural detoxification processes. It can help
                        eliminate excess waste and promote a sense of lightness.</p>
                </span>
                <h2 title="foot massage description">Your Journey to Wellness:</h2>
                <h3>Holistic Approach:</h3>
                <span>
                    <p>Foot massage is a holistic therapy that addresses the whole person. It doesn't just target
                        physical issues but also has benefits for mental and emotional well-being</p>
                </span>
                <h3>Personalized Experience:</h3>
                <span>
                    <p>Each foot massage session can be tailored to your specific needs. Whether you're seeking pain
                        relief, relaxation, or a general sense of wellness, your practitioner can customize the
                        experience for you.</p>
                </span>
                <h3>Self-Care:</h3>
                <span>
                    <p>Beyond the massage studio, foot massage techniques can be used for self-care. Learning these
                        methods allows individuals to perform simple but effective foot massages at home.</p>
                </span>
                <h3>Unlocking Wellness Through Your Feet:</h3>
                <span>
                    <p>Foot massage isn't just about pampering yourself, but about unlocking the natural healing
                        potential within your body. This ancient practice invites you to connect with your body's inner
                        wisdom and supports your journey to wellness, one step at a time.</p>
                </span>
            </div>
        </div>
        <div class="item shvedy">
            <div class="itemdescript">
                <p>Shvedy</p>
            </div>
            <div class="description">
                <h2 title="classic massage description">Classic Massage: Restoring the Balance of Body and Mind</h2>
                <h3>The Art of Relaxation and Healing:</h3>
                <span>
                    <p>Classic massage, also known as Swedish massage, is a timeless therapeutic practice that brings
                        profound relaxation, healing, and balance to both the body and mind. This description
                        illuminates the soothing and rejuvenating experience that is the hallmark of classic massage:
                    </p>
                </span>
                <h3>Rooted in Tradition:</h3>
                <span>
                    <p>Classic massage has a rich history that dates back to the early 19th century. Per Henrik Ling, a
                        Swedish fencing master, developed the techniques that form the foundation of this practice.
                        Hence, it is also referred to as Swedish massage.</p>
                </span>
                <h3>Foundations of Relaxation:</h3>
                <span>
                    <p>Classic massage is characterized by its long, gliding strokes, kneading, friction, tapping, and
                        gentle stretching techniques. These fundamental movements work in harmony to soothe the body and
                        create a deep sense of relaxation.</p>
                </span>
                <h2 title="key elements of massage">The Key Elements of Classic Massage:</h2>
                <h3>Effleurage:</h3>
                <span>
                    <p>This gentle, sweeping stroke involves gliding the hands over the skin, promoting relaxation and
                        improving circulation.</p>
                </span>
                <h3>Petrissage:</h3>
                <span>
                    <p>Kneading and rolling motions gently compress and release muscles. This technique is ideal for
                        relieving muscle tension.</p>
                </span>
                <h3>Friction:</h3>
                <span>
                    <p>Using circular movements, the therapist applies concentrated pressure to specific muscle areas.
                        It helps to break down knots and adhesions within the muscles.</p>
                </span>
                <h3>Tapotement:</h3>
                <span>
                    <p>This involves rhythmic tapping, cupping, and hacking actions. It's invigorating and can relieve
                        muscle tension.</p>
                </span>
                <h3>Stretching:</h3>
                <span>
                    <p>Passive joint movement and gentle stretching are used to improve flexibility and mobility.</p>
                </span>
                <h2 title="The Holistic Benefits of Classic Massage">The Holistic Benefits of Classic Massage:</h2>
                <h3>Relaxation and Stress Reduction:</h3>
                <span>
                    <p>Classic massage is renowned for its ability to reduce stress and promote relaxation. As the
                        therapist's hands move in soothing patterns across your body, it triggers the release of
                        endorphins, the body's natural feel-good hormones, which provide a sense of peace and
                        well-being.</p>
                </span>
                <h3>Pain Relief:</h3>
                <span>
                    <p>Beyond relaxation, classic massage offers pain relief benefits. It can alleviate chronic pain,
                        reduce muscle tension, and even address conditions like sciatica and arthritis.</p>
                </span>
                <h3>Improved Circulation:</h3>
                <span>
                    <p>The rhythmic strokes in classic massage encourage better blood circulation, leading to improved
                        oxygen flow and nutrient delivery to cells. This can help in detoxifying the body and boosting
                        overall health.</p>
                </span>
                <h3>Enhanced Flexibility:</h3>
                <span>
                    <p>Stretching during the massage helps improve your range of motion, making it a great choice for
                        athletes or those looking to improve their physical performance.</p>
                </span>
                <h3>Mental Clarity:</h3>
                <span>
                    <p>Alongside the physical benefits, classic massage clears the mind. It can alleviate mental
                        fatigue, enhance mental focus, and induce a sense of calm.</p>
                </span>
                <h3>Tailored Experience:</h3>
                <span>
                    <p> Your classic massage session is a personal journey. The therapist can adjust pressure,
                        techniques, and focus areas to meet your specific needs.</p>
                </span>
                <h3>Caring Touch:</h3>
                <span>
                    <p>Classic massage is about the healing power of human touch. It provides a safe, nurturing space
                        where you can release tension and restore balance.</p>
                </span>
                <h3>The Path to Tranquility and Vitality:</h3>
                <span>
                    <p>Classic massage is an exquisite fusion of art and science, gently guiding you toward a state of
                        tranquility and vitality. It is a restorative practice that renews both body and soul, making it
                        a timeless choice for those seeking holistic wellness.</p>
                </span>
            </div>
        </div>
        <div class="item ostiopathiya">
            <div class="itemdescript">
                <p>Ostiopathiya</p>
            </div>
            <div class="description">
                <h2 title="about Osteopathy">The Essence of Osteopathy:</h2>
                <h3>Osteopathy: A Path to Holistic Healing and Well-being</h3>
                <span>
                    <p>Osteopathy is a distinctive branch of healthcare that places the body's self-healing abilities at
                        the forefront. This description explores the profound benefits and philosophy of osteopathy:</p>
                </span>
                <h3>A Holistic Approach:</h3>
                <span>
                    <p>Osteopathy is rooted in the belief that the body is a unified and self-regulating system. It
                        recognizes that the body's structure and function are intimately connected. Thus, osteopathy
                        takes a holistic approach to healthcare, addressing not just the symptoms but the underlying
                        causes of health issues.</p>
                </span>
                <h3>Gentle, Non-Invasive Techniques:</h3>
                <span>
                    <p>Osteopathic practitioners employ a wide array of gentle, hands-on techniques to diagnose and
                        treat conditions. They use their hands to understand the body's structure and function,
                        identifying areas of tension, strain, or imbalance.</p>
                </span>
                <h2 title="Key Principles of Osteopathy">Key Principles of Osteopathy:</h2>
                <h3>The Body's Self-Healing Mechanism:</h3>
                <span>
                    <p>Osteopathy acknowledges the body's innate ability to heal itself. The role of the osteopath is to
                        facilitate and enhance this natural healing process.</p>
                </span>
                <h3>Structure and Function:</h3>
                <span>
                    <p> A core principle of osteopathy is the interrelationship between the body's structure and its
                        function. The belief is that if the body's structure is sound and properly aligned, it can
                        function optimally.</p>
                </span>
                <h3>Holistic Assessment:</h3>
                <span>
                    <p>Osteopaths do not just focus on the area that is causing pain or discomfort. They consider the
                        entire body in their assessments and treatment plans, searching for the root cause of a
                        condition.</p>
                </span>
                <h3>Hands-On Care:</h3>
                <span>
                    <p>Osteopathic treatment primarily involves hands-on techniques such as osteopathic manipulative
                        therapy (OMT). This gentle, non-invasive therapy is used to encourage the body's ability to heal
                        itself.</p>
                </span>
                <h3>Musculoskeletal Alignment:</h3>
                <span>
                    <p>Osteopaths are skilled in realigning musculoskeletal structures, which can alleviate pain,
                        improve mobility, and enhance overall well-being.</p>
                </span>
                <h3>Pain Management:</h3>
                <span>
                    <p>Osteopathy is particularly effective in managing musculoskeletal pain conditions like back pain,
                        joint pain, and even headaches.</p>
                </span>
                <h3>Improved Function:</h3>
                <span>
                    <p> By addressing structural issues, osteopathy often leads to better body function. This may
                        involve improving joint mobility, reducing inflammation, and enhancing the circulatory and
                        nervous systems.</p>
                </span>
                <h3>Back and Neck Pain:</h3>
                <span>
                    <p>Osteopathy is renowned for its effectiveness in treating back and neck pain, including conditions
                        like herniated discs, sciatica, and whiplash.</p>
                </span>
                <h3>Sports Injuries:</h3>
                <span>
                    <p> Athletes often turn to osteopathy to recover from injuries, improve performance, and prevent
                        future issues.</p>
                </span>
                <h3>Digestive Disorders:</h3>
                <span>
                    <p>Some digestive issues can be related to musculoskeletal problems, and osteopathy can provide
                        relief.</p>
                </span>
                <h3>Chronic Pain:</h3>
                <span>
                    <p>Osteopathy offers a holistic approach to managing chronic pain conditions.</p>
                </span>
                <h3>Pregnancy-Related Pain:</h3>
                <span>
                    <p>Many pregnant women find relief from back pain and other discomforts through osteopathic care.
                    </p>
                </span>
                <h3>Stress and Tension:</h3>
                <span>
                    <p>Osteopathic treatments can help relieve stress-related tension and promote relaxation.</p>
                </span>
                <h3>Tailored Care:</h3>
                <span>
                    <p>Osteopathic care is highly personalized. Practitioners work with each individual to develop a
                        unique treatment plan.</p>
                </span>
                <h3>Preventive Health:</h3>
                <span>
                    <p>Osteopathy also focuses on prevention. By addressing imbalances and strains before they become
                        painful conditions, it contributes to a healthier life.</p>
                </span>
                <h3>The Journey to Whole-Body Wellness:</h3>
                <span>
                    <p>Osteopathy is more than a medical practice; it's a philosophy that sees the body as a
                        self-regulating and self-healing entity. With the gentle guidance of skilled osteopaths, it is a
                        path to comprehensive health, vitality, and a harmonious connection between the body, mind, and
                        spirit.</p>
                </span>
            </div>
        </div>
        <div class="item stone">
            <div class="itemdescript">
                <p>Stone</p>
            </div>
            <div class="description ">
                <h2 title="Key Principles of Stone Massage">Key Principles of Stone Massage:</h2>
                <h3>Stone Massage: A Path to Deep Relaxation and Healing</h3>
                <span>
                    <p>Stone massage is a therapeutic practice that combines the soothing power of hot or cold stones
                        with the techniques of massage. Here, we delve into the world of stone massage to explore its
                        benefits, techniques, and the profound sense of relaxation it provides:</p>
                </span>
                <h3>Ancient Origins:</h3>
                <span>
                    <p>Stone massage has ancient roots, with practices found in cultures around the world. Heated stones
                        have been used for centuries to ease muscle tension, induce relaxation, and promote a sense of
                        well-being.</p>
                </span>
                <h3>Balancing Energies:</h3>
                <span>
                    <p>Stone massage often incorporates the belief that stones, each with its unique energy, can
                        harmonize the body's energies. This approach aims to balance the body's physical and spiritual
                        elements.</p>
                </span>
                <h3>Thermotherapy:</h3>
                <span>
                    <p>The central principle of stone massage is thermotherapy, which involves the use of heated or
                        chilled stones. Hot stones are usually made of basalt, a type of volcanic rock, which retains
                        heat exceptionally well. The heat from the stones penetrates deep into the muscles, relaxing
                        them and enhancing blood flow. In contrast, cold stones are used to reduce inflammation and
                        soothe sore areas.</p>
                </span>
                <h3>Enhanced Relaxation:</h3>
                <span>
                    <p>The combination of heat and massage techniques provides a profound sense of relaxation, helping
                        to release tension and stress.</p>
                </span>
                <h3>Hot Stone Therapy:</h3>
                <span>
                    <p>Heated stones are placed on specific points of the body, such as along the spine, in the palms,
                        or between the toes. They are also used by the therapist to perform various massage strokes. The
                        warmth from the stones eases muscle stiffness, allowing the therapist to work more deeply and
                        effectively.</p>
                </span>
                <h3>Cold Stone Therapy:</h3>
                <span>
                    <p>Chilled stones are employed in a complementary manner. They can reduce inflammation, relieve pain
                        in sore or inflamed areas, and promote a sense of refreshment and rejuvenation.</p>
                </span>
                <h3>Massage Techniques:</h3>
                <span>
                    <p>Skilled therapists use a combination of Swedish and deep tissue massage techniques along with the
                        stones, creating a harmonious flow. The stones themselves are often smoothed and used to glide
                        over the body, providing a soothing, sensory experience.</p>
                </span>
                <h3>Stress Reduction:</h3>
                <span>
                    <p>Stone massage is renowned for its ability to reduce stress and induce a state of profound
                        relaxation. The combination of heat and skilled touch can melt away worries and tension.</p>
                </span>
                <h3>Muscle Relaxation:</h3>
                <span>
                    <p>The heat from the stones penetrates deep into muscles, promoting relaxation and reducing muscle
                        stiffness.</p>
                </span>
                <h3>Improved Circulation:</h3>
                <span>
                    <p>Stone massage enhances blood flow, which can benefit the circulatory system, boost the immune
                        system, and promote general well-being.</p>
                </span>
                <h3>Pain Relief:</h3>
                <span>
                    <p>Stone massage is known to be effective in relieving chronic pain conditions such as arthritis,
                        fibromyalgia, and musculoskeletal pain.</p>
                </span>
                <h3>Balancing Energies:</h3>
                <span>
                    <p>Beyond the physical benefits, stone massage can help balance the body's energy centers, offering
                        a sense of wholeness and harmony.</p>
                </span>
                <h3>Tailored Experience:</h3>
                <span>
                    <p>Stone massage can be personalized to individual preferences. Whether you prefer hot or cold
                        stones, the therapist can adjust the treatment to meet your needs.</p>
                </span>
                <h3>The Journey to Deep Relaxation:</h3>
                <span>
                    <p>Stone massage is not just a therapy; it's a journey to profound relaxation and rejuvenation. By
                        harmonizing the body's energies, melting away stress, and promoting balance, stone massage
                        provides a therapeutic experience that transcends the physical, touching the soul.</p>
                </span>
            </div>
        </div>
        <div class="item faceMassage">
            <div class="itemdescript">
                <p>Facial</p>
            </div>
            <div class="description">
                <h2 title="about face massage">The Art of Face Massage:</h2>
                <h3>Face Massage: A Gateway to Renewed Radiance</h3>
                <span>
                    <p>Face massage, also known as facial massage, is a rejuvenating practice that nurtures your skin,
                        revitalizes facial muscles, and promotes a sense of deep relaxation. In this exploration, we
                        dive into the world of face massage to understand its numerous benefits and the techniques that
                        enhance both inner and outer well-being.</p>
                </span>
                <h3>Ancient Beginnings:</h3>
                <span>
                    <p>Face massage is rooted in the traditions of Ayurveda, Chinese medicine, and various indigenous
                        cultures. It has been practiced for centuries to maintain youthful-looking skin, alleviate
                        stress, and stimulate energy flow.</p>
                </span>
                <h3>Holistic Approach:</h3>
                <span>
                    <p>Beyond enhancing physical appearance, face massage embraces the holistic belief that our facial
                        features and skin's condition are interconnected with our overall health and vitality.</p>
                </span>
                <h2 title="faceMassage description">Fundamental Elements of Face Massage:</h2>
                <h3>Stress Reduction:</h3>
                <span>
                    <p>One of the primary purposes of face massage is to reduce stress. Tension often accumulates in the
                        facial muscles, leading to lines, wrinkles, and a tired appearance. By releasing this tension,
                        face massage can help you feel more relaxed and rejuvenated.</p>
                </span>
                <h3>Improved Circulation:</h3>
                <span>
                    <p> Face massage enhances blood circulation to the facial tissues, which can result in a radiant
                        complexion. Increased blood flow supplies nutrients to the skin and aids in the removal of waste
                        products.</p>
                </span>
                <h3>Lymphatic Drainage:</h3>
                <span>
                    <p>Face massage includes gentle, rhythmic movements that stimulate the lymphatic system. This
                        assists in reducing puffiness and toxins, giving your face a clearer and brighter look.</p>
                </span>
                <h3>Natural Glow:</h3>
                <span>
                    <p>By promoting the skin's natural oils, face massage can provide a healthy glow. It helps in
                        maintaining moisture balance and revitalizing tired or dry skin.</p>
                </span>
                <h3>Facial Oil or Cream:</h3>
                <span>
                    <p>A nourishing oil or cream is often applied to the face before the massage begins. This provides
                        lubrication for the hands and ensures a gentle, friction-free experience.</p>
                </span>
                <h3>Massage Techniques:</h3>
                <span>
                    <p>Skilled therapists use a combination of gentle strokes, kneading, tapping, and acupressure points
                        to soothe facial muscles, release tension, and improve circulation.</p>
                </span>
                <h3>Pressure Points:</h3>
                <span>
                    <p>By pressing on specific pressure points around the face, therapists can influence energy flow and
                        relaxation. This can also help alleviate headaches and sinus issues.</p>
                </span>
                <h3>Youthful Skin:</h3>
                <span>
                    <p>Face massage can diminish the appearance of fine lines and wrinkles, promoting a more youthful
                        appearance.</p>
                </span>
                <h3>Relaxation:</h3>
                <span>
                    <p>The soothing touch of a face massage promotes relaxation, reducing stress and fatigue.</p>
                </span>
                <h3>Enhanced Skin Health:</h3>
                <span>
                    <p>It stimulates the skin's natural regenerative processes and improves elasticity, hydration, and
                        texture.</p>
                </span>
                <h3>Clear Complexion:</h3>
                <span>
                    <p>By aiding in lymphatic drainage, face massage helps reduce puffiness, breakouts, and dark circles
                        under the eyes.</p>
                </span>
                <h3>Inner Balance:</h3>
                <span>
                    <p>Beyond the physical benefits, face massage can also bring a sense of inner tranquility, allowing
                        for a holistic mind-body experience.</p>
                </span>
                <h3>Tailored to Your Needs:</h3>
                <span>
                    <p>Face massages can be tailored to your specific concerns, whether it's anti-aging, acne
                        management, or relaxation. This personalization ensures that the treatment suits your unique
                        skin type and desired outcomes.</p>
                </span>
                <h3>A Journey to Radiant Beauty:</h3>
                <span>
                    <p>Face massage is more than a beauty treatment; it's a holistic journey to renewed radiance. By
                        embracing the age-old wisdom that beauty is an outer reflection of inner well-being, face
                        massage invites you to nurture both your skin and your soul.</p>
                </span>
            </div>
        </div>
    </div>
   
    <div class="gridConteinerMobile" id="gridConteinerMobile"> <!--  option for mobile -->
        <button class="btn-prev-mobile">&lt;</button>
        <div class="aromatherapiya descripBox">
            <div class=" itemdescriptMobile">
                <h1>Aromatherapiya</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Aromatherapy" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="bumboo descripBox">
            <div class=" itemdescriptMobile">
                <h1>Bumboo</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Bamboo_massage" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="pilate descripBox">
            <div class=" itemdescriptMobile">
                <h1>Pilates</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://fr.wikipedia.org/wiki/M%C3%A9thode_Pilates" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="footMassage descripBox">
            <div class=" itemdescriptMobile">
                <h1>Reflexology</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Reflexology" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="shvedy descripBox">
            <div class=" itemdescriptMobile">
                <h1>Shvedy</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Medical_massage" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="ostiopathiya descripBox">
            <div class=" itemdescriptMobile">
                <h1>Ostiopathiy</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Osteopathy" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="stone descripBox">
            <div class=" itemdescriptMobile">
                <h1>Stone</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Stone_massage" target="_blank">about it...</a></div>
            </div>
        </div>
        <div class="faceMassage descripBox">
            <div class=" itemdescriptMobile">
                <h1>Facial</h1>
                <div><a rel="noopener" noreferrerwindow.opener href="https://en.wikipedia.org/wiki/Facial" target="_blank">about it...</a></div>
            </div>
        </div>
        <button class="btn-next-mobile">&gt;</button>
    </div>
    <!--   end of conteiner massage discriptions-->



    <footer>
        <div class="footad">
            <div>
                <h2>Share</h2>
                <div class="icons">
                    <div class="icon-box">
                        <button class="icon" id="whatsappShare">
                            <img class="icon" src="userCSS\socialIcons\whatsup.png" alt="whatsapp">
                        </button>
                    </div>
                    <div class="icon-box">
                        <button class="icon" id="facebookShare">
                            <img class="icon" src="userCSS\socialIcons\facebook.png" alt="facebook">
                        </button>
                    </div>
                    <div class="icon-box">
                        <button class="icon" id="instagramShare">
                            <img class="icon" src="userCSS\socialIcons\instagram.png" alt="instagram">
                        </button>
                    </div>
                    <div class="icon-box">
                        <button class="icon" id="tiktokShare">
                            <img class="icon" src="userCSS\socialIcons\tiktok.png" alt="tiktok">
                        </button>
                    </div>
                </div>
            </div>
            <div class="footerAddDiscrip">
                <div>WhatsApp:</div><br>
                <div>Contact Phone:</div><br>
                <div><span>email:</span></div><br>
                <div><span>development:</span></div>
            </div>
            <div>
                <div><span><a rel="noopener" noreferrerwindow.opener
                            href="whatsapp://send?phone=9720533756636">message</a></span></div><br>
                <div><span><a rel="noopener" noreferrerwindow.opener rel="noopener"
                            href="tel:+9720533756636">053-3756636</a></span></div><br>
                <div><span><a rel="noopener" noreferrerwindow.opener
                            href="mailto:optimedisra@outlook.com">optimedisra@outlook.com</a></span></div><br>
                <div><span><a rel="noopener" noreferrerwindow.opener
                            href="mailto:egordyai88@gmail.com">egordyai88@gmail.com</a></span></div>
            </div>
            <div>
                <h2>Follow Us</h2>
                <div class="icons">
                    <div class="icon-box">
                        <div class="icon">
                            <a title="youtube" rel="noopener" noreferrerwindow.opener href="https://www.youtube.com/"
                                target="_blank">
                                <div><img class="icon" src="userCSS\socialIcons\youtube.png" alt="youtube"></div>
                            </a>
                        </div>
                    </div>
                    <div class="icon-box">
                        <div class="icon">
                            <a title="facebook" rel="noopener" noreferrerwindow.opener href="https://www.facebook.com"
                                target="_blank">
                                <div><img class="icon" src="userCSS\socialIcons\facebook.png" alt="facebook"></div>
                            </a>
                        </div>
                    </div>
                    <div class="icon-box">
                        <div class="icon">
                            <a title="instagram" rel="noopener" noreferrerwindow.opener href="https://www.instagram.com"
                                target="_blank">
                                <div><img class="icon" src="userCSS\socialIcons\instagram.png" alt="instagram"></div>
                            </a>
                        </div>
                    </div>
                    <div class="icon-box">
                        <div class="icon">
                            <a title="tiktok" rel="noopener" noreferrerwindow.opener href="https://www.tiktok.com/"
                                target="_blank">
                                <div><img class="icon" src="userCSS\socialIcons\tiktok.png" alt="tiktok"></div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h2><span><a rel="noopener" noreferrerwindow.opener href="politica.html" target="_blank">Privacy
                    Policy</a></span></h2>
        <p>Haifa: moriya avenue 35</p>
        <p>tell: 0502196936</p>   
    </footer>
    <button id="scrollToTop" class="scroll-button-up">^</button>
    <script src="landingJS.js"></script>
</body>

</html>