var iSorcom = 0;
var iIAMS = 0;
var txtSorcom = "SORCOM READY-MIX CONCRETE";
var txtIAMS = "Inventory and Accounts Management System";
var speedTitle = 30;
var speedSubtitle = 20;

// Only for the "Type Writing" effect
function sorcomWriter() {
  if (iSorcom < txtSorcom.length) {
    document.getElementById("title").innerHTML += txtSorcom.charAt(iSorcom);
    iSorcom++;
    setTimeout(sorcomWriter, speedTitle);
  } else {
    setTimeout(iamsWriter, 500);
  }
}

// Same "Type Writing" effect but this one for web title
function iamsWriter() {
  if (iIAMS < txtIAMS.length) {
    document.getElementById("subtitle").innerHTML += txtIAMS.charAt(iIAMS);
    iIAMS++;
    setTimeout(iamsWriter, speedSubtitle);
  }
}

// Real functioning clock :>
function realClockTime() {
  const now = new Date();
  const options = { month: "short", day: "2-digit", year: "numeric" };
  const dateStr = now.toLocaleDateString("en-US", options).replace(",", ".");

  let hours = now.getHours().toString().padStart(2, "0");
  let minutes = now.getMinutes().toString().padStart(2, "0");
  let seconds = now.getSeconds().toString().padStart(2, "0");

  document.getElementById(
    "clock"
  ).textContent = `${dateStr} || ${hours}:${minutes}:${seconds}`;
}

// Animations and clock starts wwhen page opens/reloadss
window.onload = function () {
  sorcomWriter();
  realClockTime();
  setInterval(realClockTime, 1000); // it updates every second OwO
};
