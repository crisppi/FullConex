function sendEmail() {
    // Insira os valores reais para as seguintes variáveis:
    const host = "seu_host_smtp";
    const username = "crisppi@gmail.com";
    const password = "Guga@0401";
    const recipient = "crisppi@icloud.com";
    const subject = "Novo email";
    const body = "Conteúdo do corpo do e-mail";

    // Crie uma nova instância do PHPMailer
    const mail = new PHPMailer(true);

    // Configura as configurações do e-mail
    mail.isSMTP();
    mail.Host = host;
    mail.SMTPAuth = true;
    mail.Username = username;
    mail.Password = password;
    mail.SMTPSecure = "tls";
    mail.Port = 587;

    // Define o conteúdo do e-mail
    mail.setFrom(username, "Roberto");
    mail.addAddress(recipient);
    mail.Subject = subject;
    mail.Body = body;

    // Anexe o PDF gerado
    mail.addAttachment("path/to/your/pdf.pdf");

    // Envia o e-mail
    mail.send();

    // Mostra uma mensagem de sucesso
    alert("E-mail enviado com sucesso!");
}