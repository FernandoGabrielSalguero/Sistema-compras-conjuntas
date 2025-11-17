<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Limpia cualquier salida previa (espacios, errores, etc.)
ob_clean();

// Indicar que devolvemos JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../models/sve_cosechaMecanicaModel.php';


$modelo = new cosechaMecanicaModel();

