// Timeout.js
var idleTime = 0;

function timerIncrement() {
    idleTime += 1;
    if (idleTime >= 60) { // 60 minutos
        window.location.href = "index.php";
    }
}

document.onmousemove = resetTimer;
document.onkeydown = resetTimer;

function resetTimer() {
    idleTime = 0;
}

setInterval(timerIncrement, 60000); // ciclo a cada 1 minuto
