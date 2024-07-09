document.addEventListener("DOMContentLoaded", function () {

  const registrationForm = document.getElementById("registrationForm");  /* שמירת טופס בקונסטנטה */

  registrationForm.addEventListener("submit", function (event) {         /* הקשב לכפתור SEND      */
    if (!validateForm()) {              /* להצור שליחת טופס אם הוא לא עבר בדיקה                 */
      event.preventDefault();           /* צהצור אירועה        */
    }
  });
/*       פונקציה לבדיקת נתונים מהמשתמש      */
  function validateForm() {
    const lname = document.getElementById("fname").value;            /* קבלת שדה שם         */
    const fname = document.getElementById("lname").value;            /* קבלתץ שדה שם משפחה */
    const email = document.getElementById("email").value;            /* קבלת שדה EMAIL      */
    const pass1 = document.getElementById("pass1").value;            /* קבלת שדה PASSWORD   */
    const pass2 = document.getElementById("pass2").value;            /* קבלת שדה REPEATE    */
    const userTellInput = document.getElementById("userTell");
    userTellInput.addEventListener("input", function () {
        const inputValue = this.value;
        const numericValue = inputValue.replace(/\D/g, ''); // Remove non-numeric characters
        this.value = numericValue.slice(0, 10);             // Limit to 10 characters
    });
    // בדיקה שם ושם משתמש  
    if ((fname.length < 1 || fname.length > 50) || (lname.length < 1 || lname.length > 50)) {
      alert("name or last name cann't be more then 50 chars");
      return false;
    }

    // בדיקת תקינות של צורת מייל  
    if (!validateEmail(email)) {
      alert("Invalid email format");
      return false;
    }

    // Password length validation
    if (pass1.length < 8 || pass1.length > 20) {
      alert("Password must be between 8 and 20 characters");
      return false;
    }

    // בדיקת אימות סיסמה        
    if (pass1 !== pass2) {
      alert("Passwords do not match");
      return false;
    }

    return true;
  }
// בדיקת מייל 
  function validateEmail(email) {   //שליחת משתנה לפונקציה 
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;     // בדיקת צורת המייל 
    return emailRegex.test(email);
  }// תשובה
  // בדיקה למספרים 

});