<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Compras Conjuntas SVE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@angular/material@14.0.0/prebuilt-themes/indigo-pink.css">
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
</head>
<body ng-app="loginApp" ng-controller="LoginController">

<div style="width: 300px; margin: auto; padding-top: 50px;">
    <mat-card>
        <mat-card-title>Iniciar Sesión</mat-card-title>
        <?php if (isset($_GET['error'])): ?>
            <div style="color: red; margin-bottom: 10px;">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="controllers/auth.php">
            <mat-form-field style="width: 100%;">
                <mat-label>CUIT</mat-label>
                <input matInput type="text" name="cuit" required>
            </mat-form-field>
            <mat-form-field style="width: 100%;">
                <mat-label>Contraseña</mat-label>
                <input matInput type="password" name="contrasena" required>
            </mat-form-field>
            <button mat-raised-button color="primary" type="submit">Ingresar</button>
        </form>
    </mat-card>
</div>

</body>
</html>