<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= base_url() ?>">
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Acceso | Constancias</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/dgb_logo.svg"/>
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container-xl px-4">
                        <div class="row justify-content-center">
                            <div class="col-lg-5">
                                <!-- Basic login form-->
                                <div class="card shadow-lg border-0 rounded-lg mt-5">
                                    
                                    <div class="bg-img-cover" style="background-image: url('assets/img/header_login.svg')"><div style="height: 8rem"></div></div>
                                    <div class="card-body"> <hr />
                                        <!-- Login form-->
                                        <form action="<?= base_url('/Login/processLogin') ?>" method="post">
                                            <!-- Form Group (email address)-->
                                            <div class="my-3">
                                                <label class="small mb-1" for="inputUser">Correo electrónico</label>
                                                <input class="form-control" id="usuario" type="text" placeholder="Ingresa tu usuario" name="usuario" required/>
                                            </div>
                                            <!-- Form Group (password)-->
                                            <div class="mb-3">
                                                <label class="small mb-1" for="inputPassword">Usuario</label>
                                                <input class="form-control" id="password" type="password" placeholder="Ingresa tu contraseña" name="password" required/>
                                            </div>
                                            <?php if (isset($_SESSION['info'])): ?>
                                                <?php if ($_SESSION['info']['estatus'] == false){ ?>
                                                    <div class="col-12">
                                                        <div class="alert alert-red mb-0" role="alert"><?php echo $_SESSION['info']['mensaje'] ?></div>
                                                    </div>
                                                <?php } ?>
                                            <?php endif; ?>
                                            <!-- Form Group (login box)-->
                                            <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                                <button class="btn btn-primary" type="submit">Acceder</button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="footer-admin mt-auto footer-dark">
                    <div class="container-xl px-4">
                        <div class="row">
                            <div class="col-md-6 small">Copyright &copy; Dirección General de Bachillerato 2025.</div>
                            <div class="col-md-6 text-md-end small">
                                <a href="#!">Aviso de privacidad</a>
                                &middot;
                                <a href="#!">Términos y condiciones.</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
