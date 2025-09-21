// Timeout.js
var idleTime = 0;

function timerIncrement() {
    idleTime += 1;
    if (idleTime >= 2) { // 2 minutos
        window.location.href = "index.php";
    }
}

document.onmousemove = resetTimer;
document.onkeydown = resetTimer;

function resetTimer() {
    idleTime = 0;
}

setInterval(timerIncrement, 300000); // 3 minutos