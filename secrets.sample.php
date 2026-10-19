<?php
// Copy this file to secrets.php and fill in your real details. Never share or upload secrets.php.
// EMAIL (Gmail example: turn on 2-Step Verification, then create an App Password)
const SMTP_HOST='ssl://smtp.gmail.com'; const SMTP_PORT=465;   // for port 587 use 'tcp://smtp.gmail.com' and 587
const SMTP_USER='mtnsacco906@gmail.com'; const SMTP_PASS='llloskxwhfatptqd'; const SMTP_FROM='mtnsacco906@gmail.com'; const SMTP_NAME='Hospital System';
// SMS (Africa's Talking: 'sandbox' only simulates, use your live username to reach real phones)
const SMS_USER='sandbox'; const SMS_KEY=''; const SMS_SENDER='';
// true = if sending fails, write the code to otp.log so you can still test. Set false when live.
const DEV_LOG=true;
