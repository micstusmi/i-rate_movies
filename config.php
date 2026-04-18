<?php
// config.php

// Setting this to 'false' as a workaround for our development mode in order to bypass the
// unneccesary time that it would take to setup the email verification functionality.
// Unfortunately setting up SMTP configurations is very time-consuming and can be many multiple unpredictable hurdles.
// If desired at a later stage we can choose to change the valueto true to REQUIRE email verification.
define('EMAIL_VERIFICATION_REQUIRED', false);