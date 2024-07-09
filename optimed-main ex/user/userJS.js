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

  const freeTimeAllMonth = collectMeeting();

  const popup = document.getElementById("popup");
  // Function to close pop-up 
  function closePopUp() {
    popup.innerHTML = "";
  }

  const days = document.querySelectorAll('.day');  // get all days of calendar
  days.forEach(day => {
    day.addEventListener('click', () => {
      const numberDay = day.firstChild.innerText;                               // get number of day  
      const month = document.getElementById("monthName").innerText;   // get name of month 
      const year = document.getElementById("currentYear").innerText;  // get current year
      const masseurID = document.getElementById("masseurID").innerText;
      masseurID.innerHTML = "";
  

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
              <input type="hidden" name="masseurID" value="${masseurID}">
              <input type="hidden" name="meetingDay" value="${numberDay}">
              <input type="hidden" name="meetingMonth" value="${month}">
              <input type="hidden" name="meetingYear" value="${year}">
              ${selectMeetings.outerHTML}<br> <!-- Insert the select elements -->
              <button type="submit" name="createNewMeeting">Create meeting</button>
            </form>
          </div>
        </div>`;

        const popupCloseBtn = document.getElementById("popUPcloseBtn");
        popupCloseBtn.addEventListener('click', closePopUp);

      }

 

 if (freeTimeAllMonth[numberDay].length <= 12 && freeTimeAllMonth[numberDay].length > 0) {   // free day for meetings
        popup.innerHTML = `
          <div class='popup-container'>
            <div class="popupBox">
              <button id='showDayForm'>Add new Meeting</button>   
              <button id='popUPcloseBtn'>x</button>
            </div>
          </div>`;

        // Event listener for add new meeting buttons
        const dayPopBtns = document.getElementById("showDayForm");
        dayPopBtns.addEventListener('click', showNewMeetingForm);

        const popupCloseBtn = document.getElementById("popUPcloseBtn");    // btn close popup
        popupCloseBtn.addEventListener('click', closePopUp);
      } 

    });
  });
});