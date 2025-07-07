console.log("Session Timeout Script Loaded");

const sessionDuration = 15; // (3600) 60 min en segundos PRUEBA
let countdown = window.serverTimeRemaining || sessionDuration;

const warningModal = new bootstrap.Modal(document.getElementById('sessionWarningModal'));
const expiredModal = new bootstrap.Modal(document.getElementById('sessionExpiredModal'));

const timeRemainingEl = document.getElementById('timeRemaining');
const logoutSessionBtn = document.getElementById('logoutSessionBtn');
const renewSessionBtn = document.getElementById('renewSessionBtn');

const timerInterval = setInterval(() => {
  countdown--;

  if (countdown === 5) { // 60 PRUEBA
    warningModal.show();
    updateCountdownDisplay(5);// 60 PRUEBA
  }

  if (countdown < 5 && countdown > 0) {  // 60 PRUEBA: countdown < 60 && countdown
    updateCountdownDisplay(countdown);
  }

  if (countdown <= 0) {
    clearInterval(timerInterval);
    warningModal.hide();
    expiredModal.show();
    setTimeout(() => {
      window.location.href = "logout.php";
    }, 2000);
  }
}, 1000);

function updateCountdownDisplay(seconds) {
  timeRemainingEl.textContent = `${seconds} seg`;
}

logoutSessionBtn.addEventListener('click', () => {
  window.location.href = "logout.php";
});

renewSessionBtn.addEventListener('click', () => {
  fetch('renew_session.php')
    .then(response => response.text())
    .then(data => {
      countdown = sessionDuration;
      warningModal.hide();
      console.log('Sesión renovada');
    })
    .catch(err => console.error(err));
});
