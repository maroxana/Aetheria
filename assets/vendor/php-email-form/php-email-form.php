<?php
class PHP_Email_Form {
  public $to;
  public $from_name;
  public $from_email;
  public $subject;
  public $smtp = array();
  public $ajax = false;
  private $messages = array();

  public function add_message($content, $label, $priority = 0) {
    $this->messages[] = array(
      'content' => $content,
      'label' => $label,
      'priority' => $priority
    );
  }

  public function send() {
    $email_content = "";
    foreach ($this->messages as $message) {
      $email_content .= $message['label'] . ": " . $message['content'] . "\n";
    }

    $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
    $headers .= "Reply-To: " . $this->from_email . "\r\n";

    if (!empty($this->smtp)) {
      // Use SMTP to send email
      return $this->send_smtp($email_content, $headers);
    } else {
      // Use PHP mail() function to send email
      return mail($this->to, $this->subject, $email_content, $headers);
    }
  }

  private function send_smtp($email_content, $headers) {
    // SMTP configuration
    $host = $this->smtp['host'];
    $username = $this->smtp['username'];
    $password = $this->smtp['password'];
    $port = $this->smtp['port'];

    // Create the SMTP connection
    $smtp = fsockopen($host, $port);
    if (!$smtp) {
      return false;
    }

    // SMTP handshake
    fputs($smtp, "EHLO " . $host . "\r\n");
    fputs($smtp, "AUTH LOGIN\r\n");
    fputs($smtp, base64_encode($username) . "\r\n");
    fputs($smtp, base64_encode($password) . "\r\n");
    fputs($smtp, "MAIL FROM: <" . $this->from_email . ">\r\n");
    fputs($smtp, "RCPT TO: <" . $this->to . ">\r\n");
    fputs($smtp, "DATA\r\n");
    fputs($smtp, $headers . "\r\n" . $email_content . "\r\n.\r\n");
    fputs($smtp, "QUIT\r\n");

    // Close the SMTP connection
    fclose($smtp);

    return true;
  }
}
?>