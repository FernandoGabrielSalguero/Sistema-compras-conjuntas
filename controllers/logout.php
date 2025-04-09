<?php
// controllers/logout.php

session_start();
session_unset();
session_destroy();

header('Location: /views/sve/login.php');
exit;
