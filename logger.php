<?php
// logger.php
// Este archivo es opcional: el manejo real se hace en core/bootstrap/request_logger.php
// Si el servidor intenta servirlo como archivo aparte, delegamos al bootstrap que ya intercepta POST /logger.php
http_response_code(204);
