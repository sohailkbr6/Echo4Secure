<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = htmlspecialchars($_POST["fullName"]);
    $email = htmlspecialchars($_POST["email"]);
    $phone = htmlspecialchars($_POST["phone"]);
    $course = htmlspecialchars($_POST["course"]);
    $message = htmlspecialchars($_POST["message"]);

    // Email to admin
    $toAdmin = "echo4secure@gmail.com";  // change if needed
    $subjectAdmin = "📥 New Registration - $course";
    $bodyAdmin = "
        A new person registered:\n\n
        Full Name: $fullName\n
        Email: $email\n
        Phone: $phone\n
        Course/Webinar: $course\n
        Message: $message\n
    ";
    $headersAdmin = "From: noreply@echo4secure.com";

    // Email to user
    $subjectUser = "✅ Registration Confirmed: $course";
    $bodyUser = "
Hi $fullName,

Thank you for registering for our \"$course\" webinar/course at ECHO4SECURE!

📅 We’ll contact you soon with schedule & joining details.
📧 If you have questions, just reply to this email.

Stay secure,  
Team Echo4Secure
https://echo4secure.com
    ";
    $headersUser = "From: ECHO4SECURE <noreply@echo4secure.com>";

    // Send both emails
    $adminSent = mail($toAdmin, $subjectAdmin, $bodyAdmin, $headersAdmin);
    $userSent = mail($email, $subjectUser, $bodyUser, $headersUser);

    if ($adminSent && $userSent) {
        echo "<script>alert('Registration successful! A confirmation email has been sent.'); window.location.href='registration.html';</script>";
    } else {
        echo "<script>alert('Something went wrong while sending emails.'); window.location.href='registration.html';</script>";
    }
}
?>
