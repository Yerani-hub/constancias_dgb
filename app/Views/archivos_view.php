<!DOCTYPE html>
<html lang="en">
    <head>
        <base href="<?= base_url() ?>">
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Constancias</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
        <link href="css/styles.css" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="assets/img/dgb_logo.svg" />
        <script data-search-pseudo-elements defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="nav-fixed">
        <nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white" id="sidenavAccordion">
            <!-- Sidenav Toggle Button-->
            <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle"><i data-feather="menu"></i></button>
            <label class="navbar-brand pe-3 ps-4 ps-lg-2" href="index.html">Dirección General de Bachillerato</label>
            
            <ul class="navbar-nav align-items-center ms-auto">
                
                    <!-- Dropdown - Search-->
                    <div class="dropdown-menu dropdown-menu-end p-3 shadow animated--fade-in-up" aria-labelledby="searchDropdown">
                        <form class="form-inline me-auto w-100">
                            <div class="input-group input-group-joined input-group-solid">
                                <input class="form-control pe-0" type="text" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2" />
                                <div class="input-group-text"><i data-feather="search"></i></div>
                            </div>
                        </form>
                    </div>
                </li>
                <!-- Alerts Dropdown-->
                <li class="nav-item dropdown no-caret d-none d-sm-block me-3 dropdown-notifications">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownAlerts" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i data-feather="bell"></i></a>
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownAlerts">
                        <h6 class="dropdown-header dropdown-notifications-header">
                            <i class="me-2" data-feather="bell"></i>
                            Notificaciones
                        </h6>
                        <!-- Example Alert 1-->
                        <a class="dropdown-item dropdown-notifications-item" href="#!">
                            <div class="dropdown-notifications-item-icon bg-warning"><i data-feather="activity"></i></div>
                            <div class="dropdown-notifications-item-content">
                                <div class="dropdown-notifications-item-content-details">29 enero de 2025</div>
                                <div class="dropdown-notifications-item-content-text">Este es un mensaje de alerta. No es nada serio, pero requiere tu atención.</div>
                            </div>
                        </a>
                        <!-- Example Alert 2-->
                        <a class="dropdown-item dropdown-notifications-item" href="#!">
                            <div class="dropdown-notifications-item-icon bg-info"><i data-feather="bar-chart"></i></div>
                            <div class="dropdown-notifications-item-content">
                                <div class="dropdown-notifications-item-content-details">29 enero de 2025</div>
                                <div class="dropdown-notifications-item-content-text">Reporte de sistema.Haz click para ver.</div>
                            </div>
                        </a>
                        <!-- Example Alert 3-->
                        <a class="dropdown-item dropdown-notifications-item" href="#!">
                            <div class="dropdown-notifications-item-icon bg-danger"><i class="fas fa-exclamation-triangle"></i></div>
                            <div class="dropdown-notifications-item-content">
                                <div class="dropdown-notifications-item-content-details">29 enero de 2025</div>
                                <div class="dropdown-notifications-item-content-text">Falla critica del sistema.</div>
                            </div>
                        </a>
                        <!-- Example Alert 4-->
                        <a class="dropdown-item dropdown-notifications-item" href="#!">
                            <div class="dropdown-notifications-item-icon bg-success"><i data-feather="user-plus"></i></div>
                            <div class="dropdown-notifications-item-content">
                                <div class="dropdown-notifications-item-content-details">29 enero de 2025</div>
                                <div class="dropdown-notifications-item-content-text">Notificación de sistema.</div>
                            </div>
                        </a>
                        <a class="dropdown-item dropdown-notifications-footer" href="#!">Ver todas la notificaciones</a>
                    </div>
                </li>
                
                <!-- User Dropdown-->
                <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
                    <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="img-fluid" src="assets/img/illustrations/profiles/profile-1.png" /></a>
                    <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up" aria-labelledby="navbarDropdownUserImage">
                        <h6 class="dropdown-header d-flex align-items-center">
                            <img class="dropdown-user-img" src="assets/img/illustrations/profiles/profile-1.png" />
                            <div class="dropdown-user-details">
                                <div class="dropdown-user-details-name">
                                    <?php 
                                        $session = session();
                                        echo $session->get('usuario'); 
                                    ?>
                                </div>
                            </div>
                        </h6>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('index.php/Login/logout') ?>">
                            <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                            Cerrar sesión
                        </a>
                    </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sidenav shadow-right sidenav-light">
                    <div class="sidenav-menu">
                        <div class="nav accordion" id="accordionSidenav">
                            <!-- Sidenav Menu Heading (Account)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            <div class="sidenav-menu-heading d-sm-none">Account</div>
                            <!-- Sidenav Link (Alerts)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            <a class="nav-link d-sm-none" href="#!">
                                <div class="nav-link-icon"><i data-feather="bell"></i></div>
                                Alerts
                                <span class="badge bg-warning-soft text-warning ms-auto">4 New!</span>
                            </a>
                            <!-- Sidenav Link (Messages)-->
                            <!-- * * Note: * * Visible only on and above the sm breakpoint-->
                            
                            <!-- Sidenav Menu Heading (Core)
                            <div class="sidenav-menu-heading">Core</div>
                            Sidenav Accordion (Dashboard)-->
                            <!-- Sidenav Heading (Custom)-->
                            <div class="sidenav-menu-heading">Custom</div>
                            <!-- Sidenav Accordion (Pages)-->
                            <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseDashboards" aria-expanded="false" aria-controls="collapseDashboards">
                                <div class="nav-link-icon"><i data-feather="folder"></i></div>
                                Constancias
                                <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseDashboards" data-bs-parent="#accordionSidenav">
                                <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                                    <a class="nav-link nav-link-icon" href="<?= base_url('index.php/Carga_archivos') ?>"><i class="me-2" data-feather="file-plus"></i>Nuevas...</a>
                                    <a class="nav-link nav-link-icon" href="<?= base_url('index.php/Carga_archivos/g_process') ?>"><i class="me-2" data-feather="archive"></i> Procesos</a>
                                    <a class="nav-link" href="<?= base_url('index.php/Buscar') ?>"><i class="me-2" data-feather="search"></i> Buscar</a>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <!-- Sidenav Footer-->
                    <div class="sidenav-footer">
                        <div class="sidenav-footer-content">
                            <div class="sidenav-footer-subtitle">Registrado como:</div>
                            <div class="sidenav-footer-title">
                                <?php 
                                    $session = session();
                                    echo $session->get('usuario'); 
                                ?>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">

                <main>
                    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
                        <div class="container-xl px-4">
                            <div class="page-header-content pt-4">
                                <div class="row align-items-center justify-content-between">
                                    <div class="col-auto mt-4">
                                        <h1 class="page-header-title">
                                            <div class="page-header-icon"><i data-feather="search"></i></div>
                                            Búsqueda
                                        </h1>
                                        <div class="page-header-subtitle">Asistente para la gestión de constancias de la Dirección General de Bachillerato.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>

                    <!-- Main page content-->
                    <div class="container-xl px-4 mt-n10">
                        <div class="row">
                            <div class="col-xxl-12 col-xl-12 mb-4">
                                <div class="card h-100">
                                    <div class="card-body h-100 p-5">
                                        <div class="row align-items-center">
                                            <div class="col-xl-8 col-xxl-12">
                                                <div class="text-center text-xl-start text-xxl-center mb-4 mb-xl-0 mb-xxl-4">
                                                    <h1 class="text-primary">¡Consultemos!</h1>
                                                    <p class="text-gray-700 mb-0">En la siguiente sección podrás consultar los archivos, por fecha de creación.</p>
                                                </div>
                                            </div>
                                        
                                            <!-- Date Range Picker Example-->
                                            <form id="formDatePicker" action="<?= base_url('index.php/Buscar/view_files') ?>" method="POST">
                                                <div class="input-group input-group-joined" style="width: 16.5rem">
                                                    <span class="input-group-text"><i class="text-primary" data-feather="calendar"></i></span>
                                                    <input class="form-control ps-0 pointer" id="litepickerRangePlugin" name="date_range" placeholder="Selecciona el rango..." readonly />
                                                </div>
                                                <div class="tab-pane active col-12 col-md-6 my-4" id="buttonsDefaultHtml" role="tabpanel" aria-labelledby="buttonsDefaultHtmlTab">
                                                    <button class="btn btn-outline-primary me-2 my-1" type="submit">
                                                        <i class="me-2" data-feather="search"></i>Buscar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <?php if (isset($_SESSION['info'])): ?>
                                            <?php if ($_SESSION['info']['estatus'] == false){ ?>
                                                <div class="col-12">
                                                    <div class="alert alert-red mb-0" role="alert"><?php echo $_SESSION['info']['mensaje'] ?></div>
                                                </div>
                                            <?php } ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['info'])): ?>
                                            <?php if ($_SESSION['info']['estatus'] == 1 && count($_SESSION['info']['files']) <= 0){ ?>
                                                <div class="col-12">
                                                    <div class="alert alert-red mb-0" role="alert">No se encontraron archivos en las fechas indicadas</div>
                                                </div>
                                            <?php } ?>
                                        <?php endif; ?>

                                        <div class="row">
                                        <?php if (isset($_SESSION['info']['files'])): ?>
                                            <?php foreach ($_SESSION['info']['files'] as $file): ?>
                                                <div class="col-xl-3 col-md-6 mb-4">
                                                    <a href="<?= base_url('index.php/Buscar/download?file_name=' . urlencode($file["name"])) ?>" 
                                                    class="text-decoration-none" 
                                                    style="color: inherit;">
                                                        <div class="card border-start-lg border-start-primary h-100 shadow-sm">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold text-primary mb-2"><?php echo $file["name"] ?></div>
                                                                        <div><?php echo $file["size"] ?></div>
                                                                        <div class="text-xs fw-bold text-info d-inline-flex align-items-center">
                                                                            <i class="me-1" data-feather="info"></i>
                                                                            <?php echo $file["created"] ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="ms-2">
                                                                        <i class="fas fa-file fa-4x text-gray-200"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="footer-admin mt-auto footer-light">
                    <div class="container-xl px-4">
                        <div class="row">
                            <div class="col-md-6 small">Copyright &copy; Dirección General de Bachillerato 2025</div>
                            <div class="col-md-6 text-md-end small">
                                <a href="#!">Aviso de confidencialidad</a>
                                &middot;
                                <a href="#!">Términos y Condiciones</a>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/litepicker/dist/bundle.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
            // Iniciamos Litepicker solo en este campo de entrada
            const picker = new Litepicker({
                element: document.getElementById('litepickerRangePlugin'),
                singleMode: false,  // Para seleccionar un rango de fechas
                format: "YYYY-MM-DD",  // Formato que deseas mostrar
                lang: "es",
                numberOfColumns: 2,
                numberOfMonths: 2,
                onSelect: function (start, end) {
                    // Actualizamos el valor del input con las fechas seleccionadas
                    document.getElementById("litepickerRangePlugin").value = start.format("YYYY-MM-DD") + " - " + end.format("YYYY-MM-DD");
                }
            });

            // Desactivar el calendario predeterminado del navegador (si existe)
            const input = document.getElementById("litepickerRangePlugin");
            input.setAttribute("readonly", "readonly");  // Opcional: hace el campo solo de lectura
        });
        </script>
    </body>
</html>
