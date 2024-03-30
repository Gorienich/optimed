document.addEventListener("DOMContentLoaded", function () {

  // Function to collect meetings for each day of the month
  function collectMeeting() {
    const freeTimeAllMonth = {}; // Object to store free times for each day
    const freeMeetingTime = [
      '07:00', '08:00', '09:00', '10:00',
      '11:00', '12:00', '13:00', '14:00',
      '15:00', '16:00', '17:00', '18:00'
    ]; // Array of free full day meeting times

    // Loop through each day of the month
    for (let i = 1; i <= 31; i++) {
      const dayElement = document.getElementById(i.toString()); // Get the element for the current day

      if (dayElement !== null) {
        // If the day has meetings, collect them
        const meetingsExist = Array.from(dayElement.querySelectorAll('.meetingTime')).map(meetingElement => meetingElement.textContent.trim());

        if (meetingsExist.length === 0) {
          // no meetings
          freeTimeAllMonth[i] = freeMeetingTime; // free day for work time
        } else {
          // Filter out the existing meetings for the selected day
          freeTimeAllMonth[i] = freeMeetingTime.filter(time => !meetingsExist.includes(time));

          // Clear the meetingsExist array for the current day
          dayElement.querySelector('.meetingsExist').innerHTML = '';
        }
      } else {
        freeTimeAllMonth[i] = []; // No meetings for this day
      }
    }

    return freeTimeAllMonth;
  }

  const freeTimeAllMonth = collectMeeting(); // object of free time of month
/*
  Object.entries(freeTimeAllMonth).forEach(([day, meetings]) => {
    console.log(`Day ${day}: ${meetings.join(', ')}`);
  });
*/
  const popup = document.getElementById("popup");
  // Function to close pop-up 
  function closePopUp() {
    popup.innerHTML = "";
  }
  // Work day of masseur 
  const days = document.querySelectorAll('.day');  // get all days of calendar
  days.forEach(day => {
    day.addEventListener('click', () => {
      const numberDay = day.firstChild.innerText;                               // get number of day  
      const month = document.getElementById("monthName").innerText;   // get name of month 
      const year = document.getElementById("currentYear").innerText;  // get current year
      const holiday = day.children[1].innerText;

      function showNewMeetingForm() {
        const meetingsLength = freeTimeAllMonth[numberDay].length;
        const selectMeetings = document.createElement('select'); // Create <select> element
        selectMeetings.name = 'meetingsTime';                    // Set name attribute

        // Add options to select element
        for (let i = 0; i < meetingsLength; i++) {
          const option = document.createElement('option'); // Create <option> element
          option.value = freeTimeAllMonth[numberDay][i];   // Set value attribute
          option.text = freeTimeAllMonth[numberDay][i];    // Set text content
          selectMeetings.appendChild(option);              // Append option to select element
        }

        // Populate the meetingPopUp with the meeting selection form
        popup.innerHTML = `
        <div class="popup-container">
          <div class="popupBox">
            <button id='popUPcloseBtn'>x</button>
            <form method='post'>
              <p>Meeting on ${numberDay}/${month}/${year}</p>
              <input type="hidden" name="meetingDay" value="${numberDay}">
              <input type="hidden" name="meetingMonth" value="${month}">
              <input type="hidden" name="meetingYear" value="${year}">
              <input id="userTell" placeholder="Enter user tell" type="text" name="UserTell"  placeholder="enter phone number" pattern="[0-9]{10}" title="phone number have to be a 10-digit number" maxlength="10" required>
              ${selectMeetings.outerHTML}<br> <!-- Insert the select elements -->
              <button type="submit" name="newMeetingForm">Create meeting</button>
            </form>
          </div>
        </div>`;

        const popupCloseBtn = document.getElementById("popUPcloseBtn");
        popupCloseBtn.addEventListener('click', closePopUp);

      }

      if (holiday === "Holiday") {   // full day of meetings
        popup.innerHTML = `
          <div class='popup-container'>
            <div class='popupBox'>  
              <button id='popUPcloseBtn'>x</button>
              <form  method='post'>
                <input type="hidden" name="meetingDay" value="${numberDay}">
                <input type="hidden" name="meetingMonth" value="${month}">
                <input type="hidden" name="meetingYear" value="${year}">
                <button type='submit' name='cancelHoliday'>Cancel Holiday</button>
              </form>
            </div>
          </div>`;

        const popupCloseBtn = document.getElementById("popUPcloseBtn");
        popupCloseBtn.addEventListener('click', closePopUp);
      }

      // display pop-up
      if (freeTimeAllMonth[numberDay].length === 0) {   // full day of meetings
        popup.innerHTML = `
          <div class='popup-container'>
            <div class="popupBox">   
              <button id='popUPcloseBtn'>x</button>
              <form  method='post'>
                <input type="hidden" name="meetingDay" value="${numberDay}">
                <input type="hidden" name="meetingMonth" value="${month}">
                <input type="hidden" name="meetingYear" value="${year}">
                <button  type='submit' name='showDayMeetings'>show meetings</button>
              </form>
            </div>
          </div>`;

        const popupCloseBtn = document.getElementById("popUPcloseBtn");    // btn close popup
        popupCloseBtn.addEventListener('click', closePopUp);
        
      } else if (freeTimeAllMonth[numberDay].length === 12 && holiday !== "Holiday") {   // free day for meetings
        popup.innerHTML = `
          <div class='popup-container'>
            <div class="popupBox">
              <button id='showDayForm'>Add new Meeting</button>   
              <button id='popUPcloseBtn'>x</button>
              <form method='post'>
                <input type="hidden" name="meetingDay" value="${numberDay}">
                <input type="hidden" name="meetingMonth" value="${month}">
                <input type="hidden" name="meetingYear" value="${year}">
                <button  type='submit' name='newHoliday'>Create holiday</button>
              </form>
            </div>
          </div>`;

        // Event listener for add new meeting buttons
        const dayPopBtns = document.getElementById("showDayForm");
        dayPopBtns.addEventListener('click', showNewMeetingForm);

        const popupCloseBtn = document.getElementById("popUPcloseBtn");    // btn close popup
        popupCloseBtn.addEventListener('click', closePopUp);
      } else if (freeTimeAllMonth[numberDay].length < 12 && freeTimeAllMonth[numberDay].length > 0 && holiday !== "Holiday") {   // available meeting time   
        popup.innerHTML = `
          <div class='popup-container'>
            <div class="popupBox">  
              <button id='popUPcloseBtn'>x</button>
              <button id='showDayForm'>Add new Meeting</button> 
              <form method='post'>
                <input type="hidden" name="meetingDay" value="${numberDay}">
                <input type="hidden" name="meetingMonth" value="${month}">
                <input type="hidden" name="meetingYear" value="${year}">
                <button id="showAllMeetings" type='submit' name='showDayMeetings'>show meetings</button>
              </form>
            </div>
          </div>`;

        const popupCloseBtn = document.getElementById("popUPcloseBtn");    // btn close popup
        popupCloseBtn.addEventListener('click', closePopUp);

        const newMeetingPopup = document.getElementById("showDayForm");
        newMeetingPopup.addEventListener('click', showNewMeetingForm);
      }
    });

  });
});
