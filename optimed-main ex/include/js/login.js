document.addEventListener("DOMContentLoaded", function () {
  const showPopupButton = document.getElementById("showPopupButton");
  const loginForm = document.getElementById("loginForm");
  const popUp = document.getElementById("popUp");
  const popUpClose = document.getElementById("popUp-close");
  
  showPopupButton.addEventListener('click', () => {
    loginForm.style.display = "none";
    popUp.style.display = "block";
  });

  popUpClose.addEventListener('click', () => {
    loginForm.style.display = "block";
    popUp.style.display = "none";
  });
});


